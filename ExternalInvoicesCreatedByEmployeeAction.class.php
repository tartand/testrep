<?php
	/**
	 * Class ExternalInvoicesCreatedByEmployee
	 *
	 * функционал "созданные мной" во вкладке "внешние счета"
	 */
	class ExternalInvoicesCreatedByEmployeeAction extends BlockAction
	{
		public function execute()
		{
			$this->data['ExternalInvoicesCreatedByEmployee'] = true;
			
			$this->prepare();
			
			if ($this->params['submit'])
			{
				try
				{
					$date_from = new DateTime($this->params['date_from']);
					$date_from->setTime(0, 0, 0);
				}
				catch (ErrorException $error)
				{
					$date_from = new DateTime('now');
				}

				try
				{
					$date_to = new DateTime($this->params['date_to']);
					$date_to->setTime(23, 59, 59);
				}
				catch (ErrorException $error)
				{
					$date_to = new DateTime('now');
				}
				// фильтр по сотрудникам
				$employee_id = in_array($this->params['employee_id'], $this->data['available_employee_ids']) ? $this->params['employee_id'] : reset($this->data['available_employee_ids']);

				$employee_repository = new EmployeeRepository();
				$current_employee = $employee_repository->findById($employee_id);

				$identification_account = null;
				if ($this->params['entity'] && $this->params['entity_id'])
				{
					$account_repository = new AccountRepository();

					$type_id = null;

					switch ($this->params['entity'])
					{
						case 'client':
							$type_id = IdentificationAccount::TYPE_CLIENT;
							break;
						case 'driver':
							$type_id = IdentificationAccount::TYPE_DRIVER;
							break;
						case 'shipping_firm':
							$type_id = IdentificationAccount::TYPE_SHIPPING_FIRM;
							break;
					}

					$accounts = $account_repository->findAccountsByTypeAndSubject($type_id, $this->params['entity_id']);

					if ($accounts && $accounts instanceof AccountCollection)
					{
						/** @var IdentificationAccount $identification_account */
						$identification_account = reset($accounts->getIterator());
					}

					$this->data['entity'] = array(
						'id' => $identification_account->subjectArray()['id'],
						'name' => $identification_account->subjectArray()['name'],
						'entity' => $identification_account->subjectArray()['entity']
					);
				}
				
				$date_type = $this->params['date_type'];
				if (!isset($this->data['date_types'][$date_type]))
				{
					$date_type = reset(array_keys($this->data['date_types']));
				}

				$external_invoice_repository = new ExternalCreditInvoiceRepository();
				/** @var ExternalCreditInvoiceCollection $external_invoices */
				$external_invoices = $external_invoice_repository->findInvoicesByCreator(
					$current_employee,
					$date_type,
					$date_from,
					$date_to,
					$identification_account
				);

				if ($external_invoices && !$external_invoices->isEmpty())
				{
					$external_invoice_ids = [];
					/** @var ExternalCreditInvoice $external_invoice */
					foreach ($external_invoices as $external_invoice)
					{
						$external_invoice_ids[$external_invoice->id()] = $external_invoice->id();
					}

					$postDP = new PostDataProvider();
					$this->data['external_invoices_has_document_invoice'] = $postDP->getPostsBySubjects(FileDataProvider::TYPE_DOCUMENT_EXTERNAL_INVOICE, $external_invoice_ids, CategoryDataProvider::CATEGORY_EXTERNAL_INVOICE_DOCS_INVOICE);
				}

				$this->data['external_invoices'] = $external_invoices;
			}
			
			return true;
		}
		//--------------------------------------------------
		
		/**
		 * предварительная обработка данных
		 */
		private function prepare()
		{
			// по-умолчанию можно смотреть только свои
			$this->data['available_employee_ids'] = array($this->params['employee']['employee_id']);
			
			$merged_department_ids = array(
				DepartmentDataProvider::DIRECTION_EXECUTION_CONTROL,
				DepartmentDataProvider::DEPARTMENT_TRANSPORT_COST_CONTROL,
				DepartmentDataProvider::DEPARTMENT_ASSISTANT
			);
			// для отдела транспортной себестоимости даём доступ заменять друг друга
			if (in_array($this->params['employee']['department_id'], $merged_department_ids))
			{
				$employeeDP = new EmployeeDataProvider();
				$employees = $employeeDP->getEmployeesBy(
					array($this->params['employee']['office_city_id']),
					$merged_department_ids
				);

				if ($employees && is_array($employees))
				{
					$this->data['available_employee_ids'] = array_merge($this->data['available_employee_ids'], array_column($employees, 'employee_id'));
				}
			}

			// для должности "Заместитель начальника отдела обеспечения транспортом" возможность видеть счета "Ведущего специалиста по работе с поставщиками (межгород)"
			if ($this->params['employee']['position_id'] == PositionDataProvider::POSITION_TRANSPORT_PROVIDING_SUBHEAD
				&& $this->params['employee']['office_city_id'] == GeoDataProvider::CITY_MOSCOW)
			{
				$employeeDP = new EmployeeDataProvider();
				$employees = $employeeDP->getEmployeesBy(
					array($this->params['employee']['office_city_id']),
					null,
					array(PositionDataProvider::POSITION_SUPPLIER_MANAGER)
				);

				if ($employees && is_array($employees))
				{
					$this->data['available_employee_ids'] = array_merge($this->data['available_employee_ids'], array_column($employees, 'employee_id'));
				}
			}

			if ($this->params['employee']['department_id'] == DepartmentDataProvider::DEPARTMENT_TRANSPORT_COST_CONTROL)
			{
				$employeeDP = new EmployeeDataProvider();
				$employees = $employeeDP->getEmployeesBy(
					array(GeoDataProvider::CITY_MOSCOW),
					array(DepartmentDataProvider::DEPARTMENT_REGIONAL_DEVELOPMENT),
					array(PositionDataProvider::POSITION_REGION_DEVELOPMENT_SUPPORT)
				);

				if ($employees && is_array($employees))
				{
					$this->data['available_employee_ids'] = array_merge($this->data['available_employee_ids'], array_column($employees, 'employee_id'));
				}
			}

			if ($this->params['employee']['department_id'] == DepartmentDataProvider::DEPARTMENT_TRANSPORT_PROVIDING)
			{
				$employeeDP = new EmployeeDataProvider();
				$employees = $employeeDP->getEmployeesBy(
					array(GeoDataProvider::CITY_MOSCOW),
					array(DepartmentDataProvider::DEPARTMENT_TRANSPORT_COST_CONTROL)
				);

				if ($employees && is_array($employees))
				{
					$this->data['available_employee_ids'] = array_merge($this->data['available_employee_ids'], array_column($employees, 'employee_id'));
				}
			}

			$employee_repository = new EmployeeRepository();
			$this->data['available_employees'] = $employee_repository->findByIds($this->data['available_employee_ids']);
			
			$this->data['date_types'] = array(
				'draw_date' => 'дата счета',
				'receipt_date' => 'дата получения счета',
			);
		}
		//--------------------------------------------------
	}