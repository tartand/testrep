<?php
	class Employee
	{
		/** @var int */
		private $employee_id, $claim_confirm_employee_id, $main_curator_employee_id;
		/** @var int */
		private $contact_id, $main_department_id, $department_id, $position_id, $office_contact_id, $storehouse_contact_id, $company_id, $category_id;
		/** @var string */
		private $skype, $icq, $passport_number, $passport_place, $birth_place, $reason_fired;
		/** @var float Лимит списания штрафов по претензиям. */
		private $guilty_reduction_limit;
		/** @var float Остаток списания штрафов на текущий месяц. В начале месяца сбрасывается в $guilty_reduction_limit. */
		private $guilty_reduction_balance;
		/** @var string */
		private $first_name, $middle_name, $last_name;
		/** @var Employee */
		private $claim_confirm_employee;
		/** @var EmployeeCollection */
		private $employees_this_employee_can_confirm_claims;
		/** @var Department */
		private $department;
		/** @var Position */
		private $position;
		/** @var DateTimeImmutable */
		private $date;
		/** @var DateTime|null */
		private $passport_date = null, $birthday = null, $test_end_date = null, $date_fired = null;
		/** @var string */
		private $workday_start, $workday_end;
		/** @var Contact */
		private $office_contact;
		/** @var Contact|null */
		private $storehouse_contact;
		/** @var User|null */
		private $user;
		/** @var boolean|null */
		private $is_fired;
		/** @var Company|null */
		private $company;
		/** @var Category|null */
		private $category;
		/** @var Contact */
		private $contact;
		/** @var int|null */
		private $external_invoice_confirm_employee_id;
		/** @var Employee|null */
		private $external_invoice_confirm_employee;

		/** @var AbsenceCollection */
		private $absences;

		/** @var SalaryCollection */
		private $salaries, $charges;
		
		/**
		 * Employee constructor.
		 * @param null $employee_id
		 * @param User $user
		 */
		public function __construct($employee_id = null, User $user)
		{
			$this->employee_id = $employee_id;
			$this->user = $user;
		}
		//-------------------------------------------------------

		public function setContactId($contact_id)
		{
			$this->contact_id = $contact_id;
		}
		//-------------------------------------------------------

		/**
		 * прямой сэтер контакта с информацией по сотруднику - использовать при создании карточки
		 * @param Contact $contact
		 */
		public function setContact(Contact $contact)
		{
			$this->contact = $contact;
		}
		//-------------------------------------------------------

		public function setPassportDate(DateTime $passport_date)
		{
			$this->passport_date = $passport_date;
		}
		//-------------------------------------------------------

		public function setBirthday(DateTime $birthday_date)
		{
			$this->birthday = $birthday_date;
		}
		//-------------------------------------------------------

		public function setDateCreate(DateTimeImmutable $date_create)
		{
			$this->date = $date_create;
		}
		//-------------------------------------------------------

		public function setTestEndDate(DateTime $test_end_date)
		{
			$this->test_end_date = $test_end_date;
		}
		//-------------------------------------------------------

		public function setDateFired(DateTime $date_fired)
		{
			$this->date_fired = $date_fired;
		}
		//-------------------------------------------------------

		public function setIsFired($is_fired)
		{
			$this->is_fired = $is_fired;
		}
		//-------------------------------------------------------

		public function setFirstName($first_name)
		{
			$this->first_name = $first_name;
		}
		//-------------------------------------------------------

		public function setMiddleName($middle_name)
		{
			$this->middle_name = $middle_name;
		}
		//-------------------------------------------------------

		public function setLastName($last_name)
		{
			$this->last_name = $last_name;
		}
		//-------------------------------------------------------

		public function setMainDepartmentId($main_department_id)
		{
			$this->main_department_id = $main_department_id;
		}
		//-------------------------------------------------------

		public function setDepartmentId($department_id)
		{
			$this->department_id = $department_id;
		}
		//-------------------------------------------------------

		public function setPositionId($position_id)
		{
			$this->position_id = $position_id;
		}
		//-------------------------------------------------------

		public function setOfficeContactId($office_contact_id)
		{
			$this->office_contact_id = $office_contact_id;
		}
		//-------------------------------------------------------

		public function setStorehouseContactId($storehouse_contact_id)
		{
			$this->storehouse_contact_id = $storehouse_contact_id;
		}
		//-------------------------------------------------------

		public function setClaimConfirmEmployeeId($claim_confirm_employee_id)
		{
			$this->claim_confirm_employee_id = $claim_confirm_employee_id;
		}
		//-------------------------------------------------------

		public function setMainCuratorEmployeeId($main_curator_employee_id)
		{
			$this->main_curator_employee_id = $main_curator_employee_id;
		}
		//-------------------------------------------------------

		public function setCompanyId($company_id)
		{
			$this->company_id = $company_id;
		}
		//-------------------------------------------------------

		public function setCategoryId($category_id)
		{
			$this->category_id = $category_id;
		}
		//-------------------------------------------------------
		
		public function setSkype($skype)
		{
			$this->skype = $skype;
		}
		//-------------------------------------------------------
		
		public function setIcq($icq)
		{
			$this->icq = $icq;
		}
		//-------------------------------------------------------
		
		public function setPassportNumber($passport_number)
		{
			$this->passport_number = $passport_number;
		}
		//-------------------------------------------------------
		
		public function setPassportPlace($passport_place)
		{
			$this->passport_place = $passport_place;
		}
		//-------------------------------------------------------
		
		public function setBirthPlace($birth_place)
		{
			$this->birth_place = $birth_place;
		}
		//-------------------------------------------------------
		
		public function setReasonFired($reason_fired)
		{
			$this->reason_fired = $reason_fired;
		}
		//-------------------------------------------------------
		
		public function setWorkdayStart($workday_start)
		{
			$this->workday_start = $workday_start;
		}
		//-------------------------------------------------------
		
		public function setWorkdayEnd($workday_end)
		{
			$this->workday_end = $workday_end;
		}
		//-------------------------------------------------------
		
		public function setExternalInvoiceConfirmEmployeeId($external_invoice_confirm_employee_id)
		{
			$this->external_invoice_confirm_employee_id = $external_invoice_confirm_employee_id;
		}
		//-------------------------------------------------------

		/**
		 * id сотрудника
		 * @return number
		 */
		public function id()
		{
			return $this->employee_id;
		}
		//-------------------------------------------------------

		public function contactId()
		{
			return $this->contact_id;
		}
		//-------------------------------------------------------
		
		/**
		 * контактная информация сотрудника
		 */
		public function contact()
		{
			if (!$this->contact && $this->contact_id)
			{
				$contact_repository = new ContactRepository();
				$this->contact = $contact_repository->findById($this->contact_id);
			}
			
			return $this->contact;
		}
		//-------------------------------------------------------

		/**
		 * имя
		 * @return string
		 */
		public function firstName()
		{
			return $this->first_name;
		}
		//-------------------------------------------------------

		/**
		 * отчество
		 * @return string
		 */
		public function middleName()
		{
			return $this->middle_name;
		}
		//-------------------------------------------------------

		/**
		 * фамилия
		 * @return string
		 */
		public function lastName()
		{
			return $this->last_name;
		}
		//-------------------------------------------------------

		/**
		 * ФИО полное.
		 * @return string
		 */
		public function fullName()
		{
			return implode(' ', array_filter(array($this->lastName(), $this->firstName(), $this->middleName())));
		}
		//-------------------------------------------------------

		/**
		 * ФИО сокращённое.
		 * @return string
		 */
		public function shortName()
		{
			$result = array($this->lastName());
			if ($this->firstName())
				$result[] = mb_substr($this->firstName(), 0, 1) . '.';
			if ($this->middleName())
				$result[] = mb_substr($this->middleName(), 0, 1) . '.';

			return implode(' ', $result);
		}
		//-------------------------------------------------------

		/**
		 * id дирекции сотрудника
		 * @return number
		 */
		public function mainDepartmentId()
		{
			return $this->main_department_id;
		}
		//-------------------------------------------------------

		/**
		 * id отдела сотрудника
		 * @return number
		 */
		public function departmentId()
		{
			return $this->department_id;
		}
		//-------------------------------------------------------

		/**
		 * отдел
		 * @return Department
		 */
		public function department()
		{
			if (!$this->department)
			{
				$department_repository = new DepartmentRepository();
				$this->department = $department_repository->findById($this->departmentId());
			}

			return $this->department;
		}
		//-------------------------------------------------------

		/**
		 * id должности сотрудника
		 * @return number
		 */
		public function positionId()
		{
			return $this->position_id;
		}
		//-------------------------------------------------------

		/**
		 * должность сотрудника
		 * @return Position
		 */
		public function position()
		{
			if (!$this->position)
			{
				$position_repository = new PositionRepository();
				$this->position = $position_repository->findById($this->positionId());
			}

			return $this->position;
		}
		//-------------------------------------------------------

		/**
		 * id пользователя
		 * @return number
		 */
		public function userId()
		{
			return $this->user()->id();
		}
		//-------------------------------------------------------

		/**
		 * дата выдачи паспорта
		 * @return DateTime|NULL
		 */
		public function passportDate()
		{
			return $this->passport_date;
		}
		//-------------------------------------------------------

		/**
		 * дата рождения
		 * @return DateTime|NULL
		 */
		public function birthday()
		{
			return $this->birthday;
		}
		//-------------------------------------------------------

		/**
		 * дата создания карточки сотрудника
		 * @return DateTime|NULL
		 */
		public function dateCreate()
		{
			return $this->date;
		}
		//-------------------------------------------------------

		/**
		 * дата окончания испытательного срока
		 * @return DateTime|NULL
		 */
		public function testEndDate()
		{
			return $this->test_end_date;
		}
		//-------------------------------------------------------

		/**
		 * дата увольнения
		 * @return DateTime|NULL
		 */
		public function dateFired()
		{
			return $this->date_fired;
		}
		//-------------------------------------------------------

		/**
		 * уволен или нет
		 * @return boolean
		 */
		public function isFired()
		{
			return $this->is_fired;
		}
		//-------------------------------------------------------

		/**
		 * пользователь
		 * @return User
		 */
		public function user()
		{
			return $this->user;
		}
		//-------------------------------------------------------

		/**
		 * id сотрудника-подтверждающего по претензиям
		 * @return number
		 */
		public function claimConfirmEmployeeId()
		{
			return $this->claim_confirm_employee_id;
		}
		//-------------------------------------------------------

		/**
		 * сотрудник, являющийся подтверждающим по претензиям
		 * @return Employee
		 */
		public function claimConfirmEmployee()
		{
			if (!$this->claim_confirm_employee)
			{
				$employee_repository = new EmployeeRepository();
				$this->claim_confirm_employee = $employee_repository->findById($this->claim_confirm_employee_id);
			}

			return $this->claim_confirm_employee;
		}
		//-------------------------------------------------------

		/**
		 * id офиса работы сотрудника
		 * @return number
		 */
		public function officeContactId()
		{
			return $this->office_contact_id;
		}
		//-------------------------------------------------------

		public function storehouseContactId()
		{
			return $this->storehouse_contact_id;
		}
		//-------------------------------------------------------

		/**
		 * Город офиса работы сотрудника
		 * @return number
		 */
		public function officeCityId()
		{
			if (!$this->officeContact())
				return null;

			return $this->officeContact()->city()->id();
		}
		//-------------------------------------------------------

		/**
		 * офис работы сотрудника
		 * @return Contact
		 */
		public function officeContact()
		{
			if (!$this->office_contact)
			{
				$contact_repository = new ContactRepository();
				$this->office_contact = $contact_repository->findById($this->office_contact_id);
			}

			return $this->office_contact;
		}
		//-------------------------------------------------------

		/**
		 * склад работы сотрудника
		 * @return Contact|null
		 */
		public function storehouse()
		{
			if (!$this->storehouse_contact)
			{
				$contact_repository = new ContactRepository();
				$this->storehouse_contact = $contact_repository->findById($this->storehouse_contact_id);
			}

			return $this->storehouse_contact;
		}
		//-------------------------------------------------------

		/**
		 * @param float $value
		 */
		public function setGuiltyReductionLimit($value)
		{
			$this->guilty_reduction_limit = $value;
		}
		//-------------------------------------------------------

		/**
		 * @return float
		 */
		public function guiltyReductionLimit()
		{
			return $this->guilty_reduction_limit;
		}
		//-------------------------------------------------------

		/**
		 * @param float $value
		 */
		public function setGuiltyReductionBalance($value)
		{
			$this->guilty_reduction_balance = $value;
		}
		//-------------------------------------------------------

		/**
		 * @return float
		 */
		public function guiltyReductionBalance()
		{
			return $this->guilty_reduction_balance;
		}
		//-------------------------------------------------------

		/**
		 * список сотрудников заместителем по подтверждению претензий который является текущий сотрудник
		 * @return EmployeeCollection
		 */
		public function employeesThisEmployeeCanConfirmClaims()
		{
			if (!$this->employees_this_employee_can_confirm_claims)
			{
				$employee_repository = new EmployeeRepository();
				$this->employees_this_employee_can_confirm_claims = $employee_repository->findEmployeesThisEmployeeCanConfirmClaims($this->id());
			}

			return $this->employees_this_employee_can_confirm_claims;
		}
		//-------------------------------------------------------

		public function mainCuratorEmployeeId()
		{
			return $this->main_curator_employee_id;
		}
		//-------------------------------------------------------

		/**
		 * id компании, на которую работает сотрудник
		 * @return number
		 */
		public function companyId()
		{
			return $this->company_id;
		}
		//-------------------------------------------------------

		public function company()
		{
			if (!$this->company)
			{
				$company_repository = new CompanyRepository();
				$this->company = $company_repository->findById($this->company_id);
			}

			return $this->company;
		}
		//-------------------------------------------------------

		/**
		 * id категории сотрудника
		 * @return number
		 */
		public function categoryId()
		{
			return $this->category_id;
		}
		//-------------------------------------------------------
		
		/**
		 * категория сотрудника
		 * @return Category|NULL
		 */
		public function category()
		{
			if (!$this->category && $this->category_id)
			{
				$category_repository = new CategoryRepository();
				$this->category = $category_repository->findById($this->category_id);
			}
			
			return $this->category;
		}
		//-------------------------------------------------------
		
		/**
		 * номер skype
		 * @return string
		 */
		public function skype()
		{
			return $this->skype;
		}
		//-------------------------------------------------------
		
		/**
		 * номер icq
		 * @return string
		 */
		public function icq()
		{
			return $this->icq;
		}
		//-------------------------------------------------------
		
		/**
		 * серия и номер паспорта
		 * @return string
		 */
		public function passportNumber()
		{
			return $this->passport_number;
		}
		//-------------------------------------------------------
		
		/**
		 * место выдачи паспорта
		 * @return string
		 */
		public function passportPlace()
		{
			return $this->passport_place;
		}
		//-------------------------------------------------------
		
		/**
		 * место рождения
		 * @return string
		 */
		public function birthPlace()
		{
			return $this->birth_place;
		}
		//-------------------------------------------------------
		
		/**
		 * причина увольнения
		 * @return string
		 */
		public function reasonFired()
		{
			return $this->reason_fired;
		}
		//-------------------------------------------------------
		
		/**
		 * начало рабочего дня hh:ii
		 * @return string
		 */
		public function workdayStart()
		{
			return $this->workday_start;
		}
		//-------------------------------------------------------
		
		/**
		 * конец рабочего дня hh:ii
		 * @return string
		 */
		public function workdayEnd()
		{
			return $this->workday_end;
		}
		//-------------------------------------------------------
		
		/**
		 * пропуски сотрудника
		 * @return AbsenceCollection
		 */
		public function absences()
		{
			if (!$this->absences)
			{
				$absence_repository = new AbsenceRepository();
				$this->absences = $absence_repository->findByEmployeeId($this->employee_id);
			}
			
			return $this->absences;
		}
		//-------------------------------------------------------
		
		/**
		 * id сотрудника, заместителя по подтверждению внешних счетов
		 * @return number
		 */
		public function externalInvoiceConfirmEmployeeId()
		{
			return $this->external_invoice_confirm_employee_id;
		}
		//-------------------------------------------------------

		/**
		 * заместитель по подтверждению внешних счетов
		 * @return Employee|NULL
		 */
		public function externalInvoiceConfirmEmployee()
		{
			if (!$this->external_invoice_confirm_employee && $this->external_invoice_confirm_employee_id)
			{
				$employee_repository = new EmployeeRepository();
				$this->external_invoice_confirm_employee = $employee_repository->findById($this->external_invoice_confirm_employee_id);
			}

			return $this->external_invoice_confirm_employee;
		}
		//-------------------------------------------------------

		/**
		 * оклады сотрудника
		 * @return SalaryCollection
		 */
		public function salaries()
		{
			if (!$this->salaries)
			{
				$salary_repository = new SalaryRepository();
				$this->salaries = $salary_repository->findByEmployeeId($this->employee_id, array(
					Salary::TYPE_TOTAL,
					Salary::TYPE_WHITE
				));
			}

			return $this->salaries;
		}
		//-------------------------------------------------------

		/**
		 * надбавки/вычеты сотрудника
		 * @return SalaryCollection
		 */
		public function charges()
		{
			if (!$this->charges)
			{
				$salary_repository = new SalaryRepository();
				$this->charges = $salary_repository->findByEmployeeId($this->employee_id, array(
					Salary::TYPE_BONUS,
					Salary::TYPE_PENALTY,
					Salary::TYPE_BENEFIT,
					Salary::TYPE_VACATION,
					Salary::TYPE_EDUCATIONAL_LEAVE
				));
			}

			return $this->charges;
		}
		//-------------------------------------------------------
	}
?>