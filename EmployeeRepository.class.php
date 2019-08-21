<?php
	class EmployeeRepository
	{
		/** @var EmployeeDataProvider */
		private $data_provider;

		public function __construct()
		{
			$this->data_provider = new EmployeeDataProvider();
		}
		//--------------------------------------------------

		// @todo метод не реализован
		private function add(Employee $employee)
		{
			return null;
		}
		//--------------------------------------------------

		private function update(Employee $employee)
		{
			$transaction = $this->data_provider->startTransaction();

			$params = array();
			$params['date'] = $employee->dateCreate() ? $employee->dateCreate()->format('Y-m-d H:i:s') : null;
			$params['category_id'] = $employee->categoryId();
			$params['contact_id'] = $employee->contactId();
			$params['skype'] = $employee->skype();
			$params['icq'] = $employee->icq();
			$params['passport_number'] = $employee->passportNumber();
			$params['passport_place'] = $employee->passportPlace();
			$params['passport_date'] = $employee->passportDate() ? $employee->passportDate()->format('Y-m-d') : null;
			$params['birth_place'] = $employee->birthPlace();
			$params['birthday'] = $employee->birthday() ? $employee->birthday()->format('Y-m-d') : null;
			$params['first_name'] = $employee->firstName();
			$params['middle_name'] = $employee->middleName();
			$params['last_name'] = $employee->lastName();
			$params['department_id'] = $employee->departmentId();
			$params['main_department_id'] = $employee->mainDepartmentId();
			$params['position_id'] = $employee->positionId();
			$params['office_contact_id'] = $employee->officeContactId();
			$params['storehouse_contact_id'] = $employee->storehouseContactId();
			$params['user_id'] = $employee->userId();
			$params['claim_confirm_employee_id'] = $employee->claimConfirmEmployeeId();
			$params['guilty_reduction_balance'] = $employee->guiltyReductionBalance();
			$params['guilty_reduction_limit'] = $employee->guiltyReductionLimit();
			$params['workday_start'] = $employee->workdayStart();
			$params['workday_end'] = $employee->workdayEnd();
			$params['test_end_date'] = $employee->testEndDate() ? $employee->testEndDate()->format('Y-m-d') : null;
			$params['date_fired'] = $employee->dateFired();
			$params['reason_fired'] = $employee->reasonFired();
			$params['main_curator_employee_id'] = $employee->mainCuratorEmployeeId();
			$params['company_id'] = $employee->companyId();
			$params['external_invoice_confirm_employee_id'] = $employee->externalInvoiceConfirmEmployeeId();

			if ($transaction = $this->data_provider->editEmployee($employee->id(), $params))
			{
				// сохранение дополнительных полей
				if (!$this->saveUser($employee)) $transaction = false;
				if (!$this->saveAbsences($employee)) $transaction = false;
				if (!$this->saveSalaries($employee)) $transaction = false;
				if (!$this->saveCharges($employee)) $transaction = false;
			}
			else $transaction = false;

			if ($transaction)
			{
				$this->data_provider->commit();
				$this->saveHistory($employee, $params);
				return $employee;
			}
			else
			{
				$this->data_provider->rollback();
				return null;
			}
		}
		//--------------------------------------------------

		public function save(Employee $employee)
		{
			if ($employee->id())
				return $this->update($employee);
			else
				return $this->add($employee);
		}
		//--------------------------------------------------

		public function delete(Employee $employee) {}
		//--------------------------------------------------

		/**
		 * сохранение пользователя/групп доступа
		 *
		 * @param Employee $employee
		 * @return bool
		 */
		private function saveUser(Employee $employee)
		{
			$user = $employee->user();
			$user = $user;

			return true;
		}
		//--------------------------------------------------

		/**
		 * сохранение пропусков
		 * @param Employee $employee
		 */
		private function saveAbsences(Employee $employee)
		{
			$absence_repository = new AbsenceRepository();
			$old_employee = $this->findById($employee->id());

			// добавление новых пропусков
			/** @var Absence $absence */
			foreach ($employee->absences() as $absence)
			{
				/** @var Absence $old_absence */
				foreach ($old_employee->absences() as $old_absence)
				{
					if ($old_absence->employeeId() == $absence->employeeId()
						&& $old_absence->dateFrom()->format('Y-m-d') == $absence->dateFrom()->format('Y-m-d')
						&& $old_absence->dateTo()->format('Y-m-d') == $absence->dateTo()->format('Y-m-d'))
					{
						continue 2;
					}
				}

				// если в старых пропусках не найдено текущий, то это добавление
				$absence_repository->save($absence);
			}

			// удаление удаленных
			/** @var Absence $old_absence */
			foreach ($old_employee->absences() as $old_absence)
			{
				/** @var Absence $absence */
				foreach ($employee->absences() as $absence)
				{
					if ($old_absence->employeeId() == $absence->employeeId()
						&& $old_absence->dateFrom()->format('Y-m-d') == $absence->dateFrom()->format('Y-m-d')
						&& $old_absence->dateTo()->format('Y-m-d') == $absence->dateTo()->format('Y-m-d'))
					{
						continue 2;
					}
				}

				// если в новых пропусках не найден пропуск из старого списка, то это удаление
				$absence_repository->delete($old_absence);
			}

			return true;
		}
		//--------------------------------------------------

		/**
		 * сохранение окладов
		 * @param Employee $employee
		 * @return boolean
		 */
		private function saveSalaries(Employee $employee)
		{
			$salary_repository = new SalaryRepository();
			$old_employee = $this->findById($employee->id());

			// добавление нового оклада
			/** @var Salary $salary */
			foreach ($employee->salaries() as $salary)
			{
				/** @var Salary $old_salary */
				foreach ($old_employee->salaries() as $old_salary)
				{
					if ($old_salary->employeeId() == $salary->employeeId()
						&& $old_salary->dateFrom()->format('Y-m-d') == $salary->dateFrom()->format('Y-m-d')
						&& $old_salary->type() == $salary->type())
					{
						continue 2;
					}
				}

				// если в старых пропусках не найдено текущий, то это добавление
				$salary_repository->save($salary);
			}

			// удаление удаленных
			/** @var Salary $old_salary */
			foreach ($old_employee->salaries() as $old_salary)
			{
				/** @var Salary $salary */
				foreach ($employee->salaries() as $salary)
				{
					if ($old_salary->employeeId() == $salary->employeeId()
						&& $old_salary->dateFrom()->format('Y-m-d') == $salary->dateFrom()->format('Y-m-d')
						&& $old_salary->type() == $salary->type())
					{
						continue 2;
					}
				}

				// если в новых пропусках не найден пропуск из старого списка, то это удаление
				$salary_repository->delete($old_salary);
			}

			return true;
		}
		//--------------------------------------------------

		private function saveCharges(Employee $employee)
		{
			$salary_repository = new SalaryRepository();
			$old_employee = $this->findById($employee->id());

			// добавление надбавки/вычета
			/** @var Salary $charge */
			foreach ($employee->charges() as $charge)
			{
				/** @var Salary $old_charge */
				foreach ($old_employee->charges() as $old_charge)
				{
					if ($old_charge->employeeId() == $charge->employeeId()
						&& $old_charge->dateFrom()->format('Y-m-d') == $charge->dateFrom()->format('Y-m-d')
						&& $old_charge->type() == $charge->type())
					{
						continue 2;
					}
				}

				// если в старых пропусках не найдено текущий, то это добавление
				$salary_repository->save($charge);
			}

			// удаление удаленных
			/** @var Salary $old_charge */
			foreach ($old_employee->charges() as $old_charge)
			{
				/** @var Salary $charge */
				foreach ($employee->charges() as $charge)
				{
					if ($old_charge->employeeId() == $charge->employeeId()
						&& $old_charge->dateFrom()->format('Y-m-d') == $charge->dateFrom()->format('Y-m-d')
						&& $old_charge->type() == $charge->type())
					{
						continue 2;
					}
				}

				// если в новых пропусках не найден пропуск из старого списка, то это удаление
				$salary_repository->delete($old_charge);
			}

			return true;
		}
		//--------------------------------------------------

		/**
		 * метод сохранения истории по сотруднику
		 * @param Employee $employee - объект сотрудника
		 * @param array $params - ранее сформированные параметры при сохранении
		 * @return boolean
		 */
		private function saveHistory(Employee $employee, $params)
		{
			// контакт
			$params['hist_contact'] = array(
				'phone' => $employee->contact()->phone(),
				'address' => $employee->contact()->address(),
				'post_address' => $employee->contact()->postAddress(),
				'email' => $employee->contact()->email(),
				'geo_id' => $employee->contact()->city() ? $employee->contact()->city()->id() : null,
				'description' => $employee->contact()->description()
			);

			// пропуска
			$params['hist_absence'] = array();
			if (!$employee->absences()->isEmpty())
			{
				/** @var Absence $absence */
				foreach ($employee->absences() as $absence)
				{
					$params['hist_absence'][] = array(
						'employee_id' => $absence->employeeId(),
						'date_from' => $absence->dateFrom()->format('Y-m-d'),
						'date_to' => $absence->dateTo()->format('Y-m-d'),
						'type' => $absence->type(),
						'comment' => $absence->comment(),
						'date' => $absence->date(),
						'creator' => $absence->userWhoAdd() ? $absence->userWhoAdd()->name() : ''
					);
				}
			}

			// зарплаты + надбавки/вычеты
			$params['hist_salary'] = array();
			if (!$employee->salaries()->isEmpty())
			{
				/** @var Salary $salary */
				foreach ($employee->salaries() as $salary)
				{
					$params['hist_salary'][] = array(
						'employee_id' => $salary->employeeId(),
						'date_from' => $salary->dateFrom()->format('Y-m-d'),
						'type' => $salary->type(),
						'value' => $salary->value(),
						'comment' => $salary->comment(),
						'date' => $salary->date()->format('Y-m-d H:i:s'),
						'creator' => $salary->nameOfEmployeeWhoAdd()
					);
				}
			}

			if (!$employee->charges()->isEmpty())
			{
				/** @var Salary $charge */
				foreach ($employee->charges() as $charge)
				{
					$params['hist_salary'][] = array(
						'employee_id' => $charge->employeeId(),
						'date_from' => $charge->dateFrom()->format('Y-m-d'),
						'type' => $charge->type(),
						'value' => $charge->value(),
						'comment' => $charge->comment(),
						'date' => $charge->date()->format('Y-m-d H:i:s'),
						'creator' => $charge->nameOfEmployeeWhoAdd()
					);
				}
			}

			$historyDP = new HistoryDataProvider();
			return $historyDP->saveEmployee($employee->id(), $params);
		}
		//--------------------------------------------------

		/**
		 * @param integer $employee_id
		 * @return Employee|null
		 */
		public function findById($employee_id)
		{
			$employee_collection = $this->findByIds(array($employee_id));

			if ($employee_collection->isEmpty())
				return null;
			else
			{
				$iterator = $employee_collection->getIterator();
				return reset($iterator);
			}
		}
		//--------------------------------------------------

		/**
		 * @param array $employee_ids
		 * @return EmployeeCollection
		 * @throws Exception
		 */
		public function findByIds(array $employee_ids)
		{
			$employee_collection = new EmployeeCollection();
			// информация по сотрудникам
			$employee_data_rows = $this->data_provider->getEmployees($employee_ids);

			// пользователи
			$user_ids = array_column($employee_data_rows, 'user_id');
			$user_repository = new UserRepository();
			$users = $user_repository->findByIds($user_ids);

			/** @var mixed $data_row */
			foreach ($employee_data_rows as $data_row)
			{
				$employee = new Employee($data_row['employee_id'], $users->offsetGet($data_row['user_id']));

				$employee->setContactId($data_row['contact_id']);
				if ($data_row['date'])
				{
					$employee->setDateCreate(new DateTimeImmutable($data_row['date']));
				}
				$employee->setCategoryId($data_row['category_id']);
				$employee->setSkype($data_row['skype']);
				$employee->setIcq($data_row['icq']);
				$employee->setPassportNumber($data_row['passport_number']);
				$employee->setPassportPlace($data_row['passport_place']);
				if ($data_row['passport_date'])
				{
					$employee->setPassportDate(new DateTime($data_row['passport_date']));
				}
				$employee->setBirthPlace($data_row['birth_place']);
				if ($data_row['birthday'])
				{
					$employee->setBirthday(new DateTime($data_row['birthday']));
				}
				$employee->setFirstName($data_row['first_name']);
				$employee->setMiddleName($data_row['middle_name']);
				$employee->setLastName($data_row['last_name']);
				$employee->setMainDepartmentId($data_row['main_department_id']);
				$employee->setDepartmentId($data_row['department_id']);
				$employee->setPositionId($data_row['position_id']);
				$employee->setOfficeContactId($data_row['office_contact_id']);
				$employee->setStorehouseContactId($data_row['storehouse_contact_id']);
				$employee->setClaimConfirmEmployeeId($data_row['claim_confirm_employee_id']);
				$employee->setGuiltyReductionBalance($data_row['guilty_reduction_balance']);
				$employee->setGuiltyReductionLimit($data_row['guilty_reduction_limit']);
				$employee->setWorkdayStart($data_row['workday_start']);
				$employee->setWorkdayEnd($data_row['workday_end']);
				if ($data_row['test_end_date'])
				{
					$employee->setTestEndDate(new DateTime($data_row['test_end_date']));
				}
				$employee->setIsFired(EmployeeDataProvider::isFired($data_row['date_fired']));
				if ($data_row['date_fired'])
				{
					$employee->setDateFired(new DateTime($data_row['date_fired']));
				}
				$employee->setReasonFired($data_row['reason_fired']);
				$employee->setMainCuratorEmployeeId($data_row['main_curator_employee_id']);
				$employee->setCompanyId($data_row['office_company_id']);
				$employee->setExternalInvoiceConfirmEmployeeId($data_row['external_invoice_confirm_employee_id']);
				$employee_collection->add($employee);
			}

			return $employee_collection;
		}
		//--------------------------------------------------

		/**
		 * @param int $user_id
		 * @return Employee|NULL
		 */
		public function findByUserId($user_id)
		{
			$employee = $this->data_provider->getEmployeeByUser($user_id);

			return $this->findById($employee['employee_id']);
		}
		//--------------------------------------------------

		/**
		 * @param array $user_ids
		 * @return EmployeeCollection|NULL
		 */
		public function findByUserIds($user_ids)
		{
			$employee = $this->data_provider->getEmployeesByUserIds($user_ids);

			return $this->findByIds(array_column($employee, 'employee_id'));
		}
		//--------------------------------------------------

		/**
		 * возвращает сотрудника, ответственного за рассмотрение претензии в городе/отделе
		 * @param int $office_city_id
		 * @param int $office_contact_id
		 * @param int $department_id
		 * @param int|null $group_id
		 * @param int|null $claim_category_id - для получения куратора, привязанного к мотивации
		 */
		public function getClaimCuratorByCityAndDepartment($office_city_id, $office_contact_id, $department_id, $group_id = null, $claim_category_id = null)
		{
			$employee_id = $this->data_provider->getClaimCuratorByCityAndDepartment($office_city_id, $office_contact_id, $department_id, $group_id, $claim_category_id);

			return $this->findById($employee_id);
		}
		//--------------------------------------------------

		/**
		 * @param int $employee_id
		 * @return EmployeeCollection
		 */
		public function findEmployeesThisEmployeeCanConfirmClaims($employee_id)
		{
			$employee_ids = $this->data_provider->getEmployeeIdsThisEmployeeCanConfirmClaims($employee_id);

			return $this->findByIds($employee_ids);
		}
		//--------------------------------------------------

		/**
		 * доступ к хоз. расчету
		 * управляет данными хоз. расчета, по которым может работать переданный пользователь
		 * сюда входит просмотр отчета "расходы/доходы по статьям" и "создание хоз. накладной"
		 * возвращает доступные офисы/статьи/тип хоз. накладной (расход/доход)
		 * если метод возвращает хотя бы один баланс офиса, то у сотрудника есть доступ создать хоз. накладную с направлением на этот офис
		 *
		 * @param Employee $employee
		 *
		 * @return AccountCollection - массив балансов
		 */
		public function getAvailableAccountsForSelfFinance(Employee $employee)
		{
			$available_accounts = array();

			$financeDP = new FinanceDataProvider();

			if (in_array(UserDataProvider::GROUP_SELF_FINANCE_CREATE, $employee->user()->groupIds()))
			{
				// все доступные балансы
				$available_accounts = $financeDP->getCompanyAccountsForPayments();
			}
			elseif ($employee->officeContactId() == ContactDataProvider::RYABINOVAYA_OFFICE_CONTACT)
			{
				$account_company_ids = array_flip(array(CompanyDataProvider::COMPANY_GL_DOSTAVKA, CompanyDataProvider::COMPANY_GLK_TRANS, CompanyDataProvider::COMPANY_GD_INTERNATIONAL, CompanyDataProvider::IP_GAVRILIN, CompanyDataProvider::COMPANY_OLD_GD));
				$account_forms = array_flip(array(Account::FORM_CASH, Account::FORM_TRANSFER));
				$account_city_ids = array_flip(array(GeoDataProvider::CITY_MOSCOW));

				$accounts = $financeDP->getCompanyAccountsForPayments();
				// фильтруем полученные балансы
				foreach ($accounts as $account)
				{
					if (!isset($account_company_ids[$account['company_id']])) continue;
					if (!isset($account_forms[$account['form']])) continue;
					if (!isset($account_city_ids[$account['office_city_id']])) continue;

					$available_accounts[] = $account;
				}
			}

			return $available_accounts;
		}
		//--------------------------------------------------

		/**
		 * Получить список групп (group_id), которые положены сотруднику по его должности и отделу.
		 *
		 * @param Employee $employee
		 * @return int[]
		 */
		public function getPrescribedGroupIds(Employee $employee)
		{
			$desks = array();

			if ($employee->positionId() == PositionDataProvider::POSITION_STOREHOUSE_MANAGER)
			{
				$desks = array(
					UserDataProvider::GROUP_TRANSPORT,
				);
			}
			if (in_array($employee->positionId(), PositionDataProvider::$positionsStorekeepers) &&
				$employee->officeCityId() == GeoDataProvider::CITY_MOSCOW
			)
			{
				$desks = array(
					UserDataProvider::GROUP_STOREHOUSE_CARGO_TAKE_IN,
				);
			}
			if (in_array($employee->positionId(), PositionDataProvider::$positionsStorekeepers) &&
				$employee->officeCityId() != GeoDataProvider::CITY_MOSCOW
			)
			{
				$desks = array(
					UserDataProvider::GROUP_STOREHOUSE_CARGO_REGIONS_TAKE_IN,
				);
			}
			if (in_array($employee->positionId(), array(
					PositionDataProvider::POSITION_DIRECTOR_OF_CUSTOMER_SERVICE,
					PositionDataProvider::POSITION_SALES_AND_MARKETING_DIRECTOR,
				)))
				$desks = array(
					UserDataProvider::GROUP_CUSTOMER,
					UserDataProvider::GROUP_SALES,
					UserDataProvider::GROUP_CALL_CENTER,
					UserDataProvider::GROUP_HEAD_CUSTOMER,
				);
			elseif ($employee->departmentId() == DepartmentDataProvider::DEPARTMENT_IT)
				$desks = array(
					UserDataProvider::GROUP_IT,
				);
			elseif ($employee->departmentId() == DepartmentDataProvider::DEPARTMENT_ACCOUNTS)
				$desks = array(
					UserDataProvider::GROUP_ACCOUNTANT,
				);
			elseif ($employee->departmentId() == DepartmentDataProvider::DEPARTMENT_DOCUMENTS
				// Проверка для филиалов.
				|| (
					$employee->departmentId() == DepartmentDataProvider::DEPARTMENT_ADMINISTRATION
					&& in_array($employee->positionId(), array(
						PositionDataProvider::POSITION_BRANCH_SUBDIRECTOR,
						PositionDataProvider::POSITION_DOCUMENT_MANAGEMENT,
						PositionDataProvider::POSITION_DOCUMENT_MANAGEMENT_HEAD,
						PositionDataProvider::POSITION_SENIOR_DOCUMENT_MANAGEMENT,
					))
				)
			)
			{
				$desks = array(
					UserDataProvider::GROUP_DOCUMENT_MANAGEMENT,
				);
			}
			elseif (
				$employee->departmentId() == DepartmentDataProvider::DEPARTMENT_DELIVERY
				// Проверка для филиалов.
				|| (
					$employee->departmentId() == DepartmentDataProvider::DEPARTMENT_ADMINISTRATION
					&& in_array($employee->positionId(), array(
						PositionDataProvider::POSITION_AVON_LOGIST_MANAGER,
						PositionDataProvider::POSITION_BRANCH_SUBDIRECTOR,
						PositionDataProvider::POSITION_DELIVERY_HEAD,
						PositionDataProvider::POSITION_DELIVERY_LOGIST,
						PositionDataProvider::POSITION_DELIVERY_MANAGER,
						PositionDataProvider::POSITION_DELIVERY_UNIVERSAL_LOGIST,
						PositionDataProvider::POSITION_FOUNDER,
						PositionDataProvider::POSITION_LOGIST_ASSISTANT,
						PositionDataProvider::POSITION_LOGIST_OPERATOR,
						PositionDataProvider::POSITION_LOGISTICS_DIRECTOR,
						PositionDataProvider::POSITION_MANAGER_LOGIST,
						PositionDataProvider::POSITION_LOGISTICS_HEAD_DEPUTY,
						PositionDataProvider::POSITION_OPERATOR_3,
						PositionDataProvider::POSITION_STOREKEEPER,
					))
				)
				// Капанадзе.
				|| (
					in_array($employee->positionId(), array(PositionDataProvider::POSITION_REGION_DEVELOPMENT_DIRECTOR))
					&& $employee->officeCityId() == GeoDataProvider::CITY_PITER
				)
			)
			{
				$desks = array(
					UserDataProvider::GROUP_DELIVERY,
				);
			}
			elseif (in_array($employee->departmentId(), array(DepartmentDataProvider::DEPARTMENT_QUALITY, DepartmentDataProvider::DEPARTMENT_BUSINESS_PROCESS_ANALYSIS)))
				$desks = array(
					UserDataProvider::GROUP_QUALITY,
				);
			elseif (in_array($employee->departmentId(), array(DepartmentDataProvider::DEPARTMENT_MARKETING, DepartmentDataProvider::DEPARTMENT_HEAD_MARKETING)))
				$desks = array(
					UserDataProvider::GROUP_MARKETING,
				);
			elseif (in_array($employee->departmentId(), array(DepartmentDataProvider::HR_DIRECTION, DepartmentDataProvider::DEPARTMENT_HR_PERSONAL)))
				$desks = array(
					UserDataProvider::GROUP_HR,
				);
			elseif ($employee->departmentId() == DepartmentDataProvider::DEPARTMENT_CLAIM)
				$desks = array(
					UserDataProvider::GROUP_CLAIM,
				);
			elseif (in_array($employee->departmentId(), array(DepartmentDataProvider::DEPARTMENT_REGIONAL_DEVELOPMENT, DepartmentDataProvider::DEPARTMENT_PARTNER_DEVELOPMENT, DepartmentDataProvider::ADMINISTRATION_DIRECTION)))
				$desks = array(
					UserDataProvider::GROUP_CUSTOMER,
					UserDataProvider::GROUP_SALES,
					UserDataProvider::GROUP_DELIVERY,
					UserDataProvider::GROUP_TRANSPORT,
					UserDataProvider::GROUP_DOCUMENT_MANAGEMENT,
					UserDataProvider::GROUP_STOREHOUSE_HEAD,
					UserDataProvider::GROUP_STOREHOUSE_MANAGEMENT,
					UserDataProvider::GROUP_REGIONAL_DEVELOPMENT,
				);
			elseif (in_array($employee->departmentId(), array(
				DepartmentDataProvider::DEPARTMENT_STOREHOUSE,
				DepartmentDataProvider::DEPARTMENT_STOREHOUSE_LOGISTIC,
				DepartmentDataProvider::DEPARTMENT_ADMINISTRATION,
			)))
			{
				if (in_array($employee->positionId(), array(
						PositionDataProvider::POSITION_STOREHOUSE_HEAD,
						PositionDataProvider::POSITION_STOREHOUSE_HEAD_2,
					)))
					$desks = array(
						UserDataProvider::GROUP_STOREHOUSE_HEAD,
					);
				elseif (in_array($employee->positionId(), array(
						PositionDataProvider::POSITION_STOREHOUSE_MANAGEMENT,
						PositionDataProvider::POSITION_MANAGER,
						PositionDataProvider::POSITION_LOGIST_OPERATOR,
						PositionDataProvider::POSITION_OPERATOR,
						PositionDataProvider::POSITION_STOREHOUSE_LOGIST,
					)))
					$desks = array(
						UserDataProvider::GROUP_STOREHOUSE_MANAGEMENT
					);
			}
			elseif ($employee->departmentId() == DepartmentDataProvider::SECURITY_SERVICE)
				$desks = array(
					UserDataProvider::GROUP_SECURITY,
				);
			elseif ($employee->departmentId() == DepartmentDataProvider::DEPARTMENT_AUDIT)
				$desks = array(
					UserDataProvider::GROUP_AUDIT,
				);
			elseif (in_array($employee->departmentId(), array(
				DepartmentDataProvider::DEPARTMENT_FINANCE,
				DepartmentDataProvider::FINANCE_DIRECTION,
				DepartmentDataProvider::DEPARTMENT_CASH,
			)))
				$desks = array(
					// UserDataProvider::GROUP_FINANCE
				);
			elseif ($employee->departmentId() == DepartmentDataProvider::JURIST_SERVICE)
				$desks = array(
					UserDataProvider::GROUP_JURIST,
					UserDataProvider::GROUP_CLAIM,
				);
			elseif ($employee->departmentId() == DepartmentDataProvider::DEPARTMENT_CALL_CENTER)
				$desks = array(
					UserDataProvider::GROUP_CALL_CENTER,
				);
			elseif (
				$employee->departmentId() == DepartmentDataProvider::DEPARTMENT_TRANSPORT_PROVIDING
				// Проверка для филиалов, в которых остаётся один отдел по операционной деятельности вместо кучи разных.
				// Помимо отдела проверяем должности, которые в разных филиалах разные.
				|| (
					$employee->departmentId() == DepartmentDataProvider::DEPARTMENT_ADMINISTRATION
					&& in_array($employee->positionId(), array(
						PositionDataProvider::POSITION_BRANCH_CONTROLLER,
						PositionDataProvider::POSITION_DELIVERY_LOGIST,
						PositionDataProvider::POSITION_DELIVERY_UNIVERSAL_LOGIST,
						PositionDataProvider::POSITION_LOGIST_ASSISTANT,
						PositionDataProvider::POSITION_LOGISTIC_DEPARTMENT_HEAD,
						PositionDataProvider::POSITION_LOGIST_OPERATOR,
						PositionDataProvider::POSITION_MANAGER_LOGIST,
						PositionDataProvider::POSITION_LOGISTICS_HEAD_DEPUTY,
						PositionDataProvider::POSITION_SENIOR_MANAGER_LOGIST,
						PositionDataProvider::POSITION_STOREHOUSE_LOGIST,
						PositionDataProvider::POSITION_TRANSPORT_LOGIST,
						PositionDataProvider::POSITION_TRANSPORT_PROVIDING_HEAD,
					))
				)
			)
			{
				$desks = array(
					UserDataProvider::GROUP_TRANSPORT,
				);
			}

			if (in_array($employee->departmentId(), array(
				DepartmentDataProvider::DEPARTMENT_SALES_DEVELOPMENT,
				DepartmentDataProvider::DEPARTMENT_COMMERCIAL,
			)))
			{
				if (in_array($employee->positionId(), array(
					PositionDataProvider::POSITION_REGIONAL_SALES_HEAD,
					PositionDataProvider::POSITION_ACTING_REGIONAL_SALES_HEAD,
				)))
				{
					$desks = array_merge($desks, array(
						UserDataProvider::GROUP_REGION_SALES,
						UserDataProvider::GROUP_HEAD,
						UserDataProvider::GROUP_CUSTOMER,
						UserDataProvider::GROUP_SALES,
					));
				}
				elseif ($employee->positionId() == PositionDataProvider::POSITION_SALES_LEADING_MANAGER)
				{
					$desks = array_merge($desks, array(
						UserDataProvider::GROUP_HEAD,
						UserDataProvider::GROUP_CUSTOMER,
						UserDataProvider::GROUP_SALES,
					));
				}
				elseif (in_array($employee->positionId(), PositionDataProvider::$positions_customer))
				{
					$desks = array_merge($desks, array(
						UserDataProvider::GROUP_CUSTOMER,
					));
				}
				else
				{
					$desks = array_merge($desks, array(
						UserDataProvider::GROUP_CUSTOMER,
						UserDataProvider::GROUP_SALES,
					));
				}
			}

			// Рег. управляющим даём стол рег. развития, даже если они не в отделе рег. развития (они могут быть в филиале в операционном отделе).
			if (in_array($employee->positionId(), array(PositionDataProvider::POSITION_REGION_DEVELOPMENT_DIRECTOR)))
				$desks[] = UserDataProvider::GROUP_REGIONAL_DEVELOPMENT;

			// Убираем дубли.
			$desks = array_unique($desks);

			return $desks;
		}
		//----------------------------------------------------------------------
	}
