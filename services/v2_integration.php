<?php
/** @var \Silex\Application $app */

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ParseException;
use GuzzleHttp\Ring\Client\StreamHandler;

/*
 * Сохранение рабочего листа и заказ-наряда
 *
 * Исходные данные
 * <code>
 * {
 *   "DataType": "Заказ-наряд",
 *   "Id": "А000000043",
 *   "Date": "18.01.2016",
 *   "ClientStatement": "ТО",
 *   "Recommendations": "",
 *   "Department": {
 *     "DataType": "Цеха",
 *     "Id": "00000   ",
 *     "Name": "Основной цех"
 *   },
 *   "Employees": [
 *     {
 *       "DataType": "Сотрудники",
 *       "Id": "Администратор",
 *       "Name": "Беликова Мария Сергеевна",
 *       "Role": "Ответственный"
 *     },
 *     {
 *       "DataType": "Сотрудники",
 *       "Id": "0000000020",
 *       "Name": "Понтус Владимир",
 *       "Role": "Мастер"
 *     },
 *     {
 *       "DataType": "Сотрудники",
 *       "Id": "0000000021",
 *       "Name": "Еремин Сергей",
 *       "Role": "Диспетчер"
 *     }
 *   ],
 *   "RepairType": {
 *     "DataType": "Виды ремонта",
 *     "Id": "ЦБ000002",
 *     "Name": "ТО"
 *   },
 *   "Client": {
 *     "DataType": "Контрагенты и контакты",
 *     "Id": "ЦБ000096",
 *     "LastName": "",
 *     "Name": "ООО Рога и копыта",
 *     "MiddleName": "",
 *     "Type": "Юридическое лицо",
 *     "Kpp": "682901001",
 *     "Inn": "",
 *     "Ogrn": "1234567890",
 *     "Addresses": [
 *       {
 *         "Addr": "",
 *         "DataType": "Адреса",
 *         "Type": "",
 *         "PostCode": "",
 *         "Region": "",
 *         "District": "",
 *         "City": "",
 *         "Street": "",
 *         "House": "",
 *         "Building": "",
 *         "Apartment": ""
 *       }
 *     ],
 *     "Contacts": [
 *       {
 *         "DataType": "Контактные лица",
 *         "Id": "КТ000000102",
 *         "Name": "Михайлов Иван Петрович",
 *         "Phones": [
 *           {
 *             "DataType": "Телефоны",
 *             "Type": "Контактный телефон",
 *             "Number": "+7(905)1234561"
 *           }
 *         ]
 *       },
 *       {
 *         "DataType": "Контактные лица",
 *         "Id": "КТ000000130",
 *         "Name": "Михайлова Юлия Геннадиевна",
 *         "Phones": [
 *           {
 *             "DataType": "Телефоны",
 *             "Type": "Контактный телефон",
 *             "Number": "+7(980)6325354"
 *           }
 *         ]
 *       }
 *     ],
 *     "Email": "",
 *     "Phones": [
 *       {
 *         "DataType": "Телефоны",
 *         "Type": "Контактный телефон",
 *         "Number": "+7(905)1234561"
 *       }
 *     ],
 *     "Documents": [
 *       {
 *         "DataType": "Подтверждающие документы",
 *         "Type": "",
 *         "Serie": "",
 *         "Number": "",
 *         "IssuedAt": "",
 *         "IssuedBy": ""
 *       }
 *     ]
 *   },
 *   "Car": {
 *     "DataType": "Автомобили",
 *     "Id": "ЦБ00000030",
 *     "VIN": "",
 *     "Model": {
 *       "DataType": "Модели автомобилей",
 *       "Id": "ЦБ00000012",
 *       "Name": "C-Crosser"
 *     },
 *     "RegNum": "",
 *     "Mileage": "",
 *     "ProductionYear": 2008,
 *     "Engine": {
 *       "DataType": "Типы двигателей",
 *       "Id": "        ",
 *       "Name": ""
 *     },
 *     "Transmission": {
 *       "DataType": "Типы КПП",
 *       "Id": "        ",
 *       "Name": ""
 *     },
 *     "Color": {
 *       "DataType": "Цвета",
 *       "Id": "        ",
 *       "Name": ""
 *     }
 *   },
 *   "Works": [
 *     {
 *       "DataType": "Работы заказ-наряда",
 *       "Id": 1,
 *       "Type": {
 *         "DataType": "Автоработы",
 *         "Id": "ЦБ00000021",
 *         "Name": "ТО-1",
 *         "StandardTime": "",
 *         "Code": "1"
 *       },
 *       "StandardTime": "1,00",
 *       "Price": "5 000,00",
 *       "Quantity": "1,00",
 *       "DiscountPercent": "",
 *       "Sum": "5 000,00"
 *     },
 *     {
 *       "DataType": "Работы заказ-наряда",
 *       "Id": 2,
 *       "Type": {
 *         "DataType": "Автоработы",
 *         "Id": "ЦБ00000015",
 *         "Name": "ТО-2",
 *         "StandardTime": "",
 *         "Code": "12"
 *       },
 *       "StandardTime": "1,00",
 *       "Price": "7 000,00",
 *       "Quantity": "1,00",
 *       "DiscountPercent": "",
 *       "Sum": "7 000,00"
 *     }
 *   ],
 *   "Materials": [
 *     {
 *       "DataType": "Номенклатура",
 *       "Id": "",
 *       "Type": "",
 *       "Price": "",
 *       "Quantity": "",
 *       "DiscountPercent": "",
 *       "Sum": ""
 *     }
 *   ],
 *   "Stage": {
 *     "DataType": "Состояния заказ-нарядов",
 *     "Id": "0000000001",
 *     "Name": "Заявка"
 *   }
 * }
 * <code>
 */

$serviceCaseSaver = function (array $data, $forClient) use ($app) {
    $data = DataArray::create($data);

    $vehicleData = array_merge($data->hash('Car'), [
        "Client" => $data->hash('Client')
    ]);

    $success = null !== $app['v2']['client_vehicle']($vehicleData, $forClient);

    $works = array_map(function ($srcData) use (&$success, $app, $forClient) {
        $srcData = DataArray::create($srcData);

        $typeData = $srcData->hash('Type');
        if (!isset($typeData["StandardTime"])) {
            $typeData["StandardTime"] = $srcData->number("StandardTime");
        }
        $success = null !== ($typeId = $app['v2']['service_work_type']($typeData, $forClient)) && $success;

        return [
            "workId" => $typeId,
            "standardTime" => $srcData->number("StandardTime"),
            "price" => $srcData->number("Price"),
            "quantity" => $srcData->number("Quantity"),
            "totalPrice" => $srcData->number('Sum'),
        ];
    }, $data->collection('Works'));

    $materials = array_map(function ($srcData) use (&$success, $app, $forClient) {
        $srcData = DataArray::create($srcData);

        return [
            "price" => $srcData->number("Price"),
            "quantity" => $srcData->number("Quantity"),
            "totalPrice" => $srcData->number('Sum'),
        ];
    }, $data->collection('Materials'));

    $success = null !== ($repairTypeId = $app['v2']['service_repair_type']($data->hash('RepairType'), $forClient)) && $success;

    $success = null !== ($departmentId = $app['v2']['department']($data->hash('Department'), $forClient)) && $success;

    $assigneeId = null;
    foreach ($data->collection('Employees') as $employeeData) {
        $employeeData['Department'] = $data->data('Department')->string('Id');
        $success = null !== ($userId = $app['v2']['user']($employeeData, $forClient)) && $success;

        $employeeData = DataArray::create($employeeData);

        if ($employeeData->string('Role') == 'Ответственный') {
            $assigneeId = $userId;
        }
    }

    if (!$success) {
        $app['logger']->withName('v2_interaction')->info(
            'Рабочий лист не пытаемся сохранять, т.к. не сохранены связанные данные'
        );

        return null;
    }

    static $statusMap = [
        "Заявка" => 'service.reminder',
        "Начать работу" => 'service.reminder',
        "В работе" => 'service.reminder',
        "Выполнен" => 'completed',
        "Закрыт" => 'completed',
    ];

    $contact = $data->data('Client')->collection('Contacts', 'filled', ['Type', 'Id', 'Phones'])[0];

    $caseData = [
        'no' => $data->string('Id'),
        'works' => $works,
        'assignee' => $assigneeId,
        'department' => $departmentId,
        'clientStatement' => $data->string('ClientStatement'),
        'activateWorkOrder' => true,
        'totalPaidForWorks' => array_sum(array_column($works, 'sum')),
        'totalPaidForSpares' => array_sum(array_column($materials, 'sum')),
        'repairType' => $repairTypeId,
        'status' => $data->data('Stage')->enum('Name', $statusMap) ?: 'service.reminder',
        'client' => ['id' => $app['association_fetcher']($forClient, 'clients', $data->data('Client')->string('Id'))],
        'contact' => ['id' => $app['association_fetcher']($forClient, 'persons', $contact['Id'])],
        'vehicle' => ['id' => $app['association_fetcher']($forClient, 'vehicles', $data->data('Car')->string('Id'))],
    ];

    return $app['v2_save']($forClient, 'serviceStation/cases', $data->string('Id'), $caseData);
};

$clientSaver = function (array $data, $forClient) use ($app) {
    $data = DataArray::create($data);

    $clientData = [
        'email' => $data->string('Email'),
        'phones' => $data->collection('Phones', 'phone'),
        'tags' => [],
    ];

    $type = $data->raw('Type') != 'Юридическое лицо' ? 'individual' : 'business';

    $addressRequiredFields = ["Type", "Region", "District", "City", "Street", "House"];
    foreach ($data->collection('Addresses', 'filled', $addressRequiredFields) as $addressData) {
        $addressData = DataArray::create($addressData);
        $addressTypeCode = mb_substr($addressData->string('Type', DataArray::STR_TOLOWER), 0, 4);

        if ($addressTypeCode == 'факт') {
            $clientData = array_merge($clientData, [
                'addrPostalCode' => $addressData->string("PostCode"),
                'addrRegionId' => $addressData->string("Region"),
                'addrDistrict' => $addressData->string("District"),
                'addrCity' => $addressData->string("City"),
                'addrStreet' => $addressData->string("Street"),
                'addrHouse' => $addressData->string("House"),
                'addrBuilding' => $addressData->string("Building"),
                'addrApartment' => $addressData->string("Apartment"),
            ]);
        } elseif ($type == 'business' && $addressTypeCode == 'юрид') {
            $clientData = array_merge($clientData, [
                'legalAddrPostalCode' => $addressData->string("PostCode"),
                'legalAddrRegionId' => $addressData->string("Region"),
                'legalAddrDistrict' => $addressData->string("District"),
                'legalAddrCity' => $addressData->string("City"),
                'legalAddrStreet' => $addressData->string("Street"),
                'legalAddrHouse' => $addressData->string("House"),
                'legalAddrBuilding' => $addressData->string("Building"),
                'legalAddrApartment' => $addressData->string("Apartment"),
            ]);
        }
    }

    if ($type == 'individual') {
        $clientData = array_merge($clientData, [
            'lastName' => $data->string('LastName'),
            'firstName' => $data->string('Name'),
            'middleName' => $data->string('MiddleName'),
            'gender' => $data->string("Gender"),
        ]);
    } else {
        $clientData = array_merge($clientData, [
            'name' => $data->string('Name'),
            "kpp" => $data->string("Kpp"),
            "ogrn" => $data->string("Ogrn"),
        ]);
    }

    foreach ($data->collection('Documents', 'filled', ["Type", "Serie", "Number"]) as $documentData) {
        $documentData = DataArray::create($documentData);
        if ('individual' == $type && $documentData->string('Type') == 'Паспорт гражданина РФ') {
            $clientData = array_merge($clientData, [
                'passportSerie' => $documentData->string("Serie"),
                'passportNumber' => $documentData->string("Number"),
                'passportIssuedAt' => $documentData->string("IssuedAt"),
                'passportIssuedBy' => $documentData->string("IssuedBy"),
            ]);
        }
    }


    if ($v2client = $app['v2_save']($forClient, 'clients', $data->string('Id'), $clientData, ['type' => $type])) {
        foreach ($data->collection('Contacts', 'filled', ['Phones']) as $personData) {
            $app['v2']['person'](array_merge($personData, ['Client' => $data->string('Id')]), $forClient);
        }
    }

    return $v2client;
};

$personSaver = function (array $data, $forClient) use ($app) {
    $data = DataArray::create($data);

    $nameParts = array_pad(preg_split('/\s+/', $data->string('Name')), 3, '');
    $personData = [
        'clientId' => $app['association_fetcher']($forClient, 'clients', $data->string('Client')),
        'middleName' => array_pop($nameParts),
        'firstName' => array_pop($nameParts),
        'lastName' => implode(' ', $nameParts),
        'phones' => $data->collection('Phones', 'phone'),
    ];

    return $app['v2_save']($forClient, 'persons', $data->string("Id"), $personData);
};

$clientVehicleSaver = function (array $data, $forClient) use ($app) {
    $data = DataArray::create($data);

    if (null === $ownerId = $app['v2']['client']($data->hash('Client'), $forClient)) {
        return null;
    }

    $modelData = $data->hash('Model');
    $vehicleData = [
        'modelId' => $app['v2']['vehicle_model']($modelData, $forClient),
        'vin' => $data->string("Vin"),
        'regNumber' => $data->string("RegNum"),
        'mileage' => $data->number("Mileage"),
        'productionYear' => $data->number("ProductionYear"),
        'clientId' => $ownerId,
    ];

    return $app['v2_save']($forClient, 'vehicles', $data->string("Id"), $vehicleData);
};

$serviceWorkTypeSaver = function (array $data, $forClient) use ($app) {
    $data = DataArray::create($data);

    return $app['v2_save']($forClient, 'serviceStation/workTypes', $data->string('Id'), [
        'code' => $data->string('Code'),
        'name' => $data->string('Name'),
        "standardTime" => $data->number('StandardTime'),
    ]);
};

$serviceRepairTypeSaver = function (array $data, $forClient) use ($app) {
    $data = DataArray::create($data);
    $repairTypeData = ['name' => $data->string("Name")];

    return $app['v2_save']($forClient, 'serviceStation/repairType', $data->string("Id"), $repairTypeData);
};

$userSaver = function (array $data, $forClient) use ($app) {
    $data = DataArray::create($data);

    return $app['v2_save']($forClient, 'users', $data->string("Id"), [
        'username' => $data->string("Name"),
        'password' => uniqid(),
        'role' => 'admin',
        'fullname' => $data->string("Name"),
        'email' => $data->string("Email"),
        'departments' => [$app['association_fetcher']($forClient, 'departments', $data->string("Department"))]
    ]);
};

$departmentSaver = function (array $data, $forClient) use ($app) {
    $data = DataArray::create($data);

    $departmentData = [
        'name' => $data->string("Name"),
        'primaryAutosalon' => $app['v2']['autosalon']([], $forClient),
    ];
    $queryParams = ['Infotech_Autocrm_Dealer_Models_Department[type]' => 'autoservice.mechanics'];

    return $app['v2_save']($forClient, 'departments', $data->string("Id"), $departmentData, $queryParams);
};

$vehicleModelGetter = function (array $data, $forClient) use ($app) {
    $data = DataArray::create($data);

    if (null !== $modelId = $app['association_fetcher']($forClient, 'models', $data->string('Id'))) {
        $result = $app['v2_send']($forClient, 'get', '/models', ['query' => ['fullName' => $data->string("Name")]]);
        if ($result /* not null and not empty array */) {
            $modelId = $result[0]['id'];
            $app['association_saver']($forClient, 'models', $data->string('Id'), $modelId);
        }
    }

    return $modelId;
};

$autosalonGetter = function (array $data, $forClient) use ($app) {
    if (null !== $modelId = $app['association_fetcher']($forClient, 'models', 'default')) {
        $result = $app['v2_send']($forClient, 'get', '/autosalons');
        if ($result /* not null and not empty array */) {
            $modelId = $result[0]['id'];
            $app['association_saver']($forClient, 'autosalons', 'default', $modelId);
        }
    }

    return $modelId;
};

// ------------------------------------------------------------

$app['v2'] = [
    'client' => $clientSaver,
    'person' => $personSaver,
    'service_case' => $serviceCaseSaver,
    'vehicle_model' => $vehicleModelGetter,
    'client_vehicle' => $clientVehicleSaver,
    'service_work_type' => $serviceWorkTypeSaver,
    'service_repair_type' => $serviceRepairTypeSaver,
    'user' => $userSaver,
    'department' => $departmentSaver,
    'autosalon' => $autosalonGetter,
];

// ============================================================

$app['v2_send'] = $app->protect(
    /**
     * @param string $clientId
     * @param string $method
     * @param string $url
     * @param array $options
     *
     * @return array|null
     */
    function ($clientId, $method, $url, $options = []) use ($app) {
        /** @var \Monolog\Logger $logger */
        $logger = $app['logger']->withName('v2_send');

        $clientConfig = $app['client_fetcher']($clientId);

        $http = new Client([
            'handler' => new StreamHandler(),
            'base_url' => $clientConfig['v2']['base_url'],
            'defaults' => [
                'debug' => $app['debug'],
                'exceptions' => false,
                'timeout' => 5, // sec
                'headers' => [
                    'X-AutoCRM-API' => '1',
                    'X-AutoCRM-Access-Token' => $clientConfig['v2']['auth_key']
                ],
            ],
        ]);

        $response = $http->send($http->createRequest($method, $url, $options));

        if (substr($response->getStatusCode(), 0, 1) !== '2') {
            $result = null;
            $logger->error(
                'Ошибка API запроса к v2. Получен ошибочный код ответа',
                [
                    'response' => [
                        'status' => $response->getStatusCode(),
                        'body' => $response->getBody()->getContents(),
                    ],
                ]
            );
        } else {
            try {
                $savedData = $response->json();
                if (!$savedData['success']) {
                    $result = null;
                    $logger->error(
                        'Ошибка API запроса к v2. API v2 вернул ошибку',
                        [
                            'response' => [
                                'status' => $response->getStatusCode(),
                                'body' => $response->json(),
                            ],
                        ]
                    );
                } else {
                    $result = $savedData['result'];
                }
            } catch (ParseException $e) {
                $result = null;
                $logger->error(
                    'Ошибка API запроса к v2. Полученный ответ не является валидным json-ом',
                    [
                        'response' => [
                            'status' => $response->getStatusCode(),
                            'body' => $response->getBody()->getContents(),
                        ],
                    ]
                );
            }
        }

        return $result;
    }
);

$app['v2_save'] = $app->protect(
    /**
     * @param string $clientId
     * @param string $type
     * @param string $id
     * @param array  $data
     * @param array  $query
     * @param string $idField
     *
     * @return null|integer
     */
    function ($clientId, $type, $id, $data, $query = [], $idField = 'id') use ($app) {
        /** @var \Monolog\Logger $logger */
        $logger = $app['logger']->withName('v2_saver');

        if ($id == '') {
            $logger->error('Не указан идентификатор при отправке данных в v2', $data);

            return null;
        }

        $crmId = $app['association_fetcher']($clientId, $type, $id);

        $url = implode('/', ['', $type, ($crmId ? 'save/' . $crmId : 'create')]);
        $options = ['query' => $query, 'json' => $data];
        $logger->debug('Отправка запроса "' . $url . '"', $options);

        $result = $app['v2_send']($clientId, 'post', $url, $options);

        if ($result === null) {
            return null;
        }
        if (empty($result[$idField])) {
            $logger->error('Не обнаружен идентификатор сохраненных данных в поле "' . $idField . '"', $result);

            return null;
        }

        $app['association_saver']($clientId, $type, $id, $result[$idField]);

        return $result[$idField];
    }
);


class DataArray {

    const STR_TOLOWER = 1;

    const PHONE_TYPE_WORK = 'W';
    const PHONE_TYPE_HOME = 'H';
    const PHONE_TYPE_MOBILE = 'M';
    const PHONE_TYPE_FAX = 'F';

    /**
     * @var array
     */
    private $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function string($field, $flags = 0)
    {
        $mods = [
            0 => 'strval',
            self::STR_TOLOWER => 'mb_strtolower'
        ];

        return isset($this->data[$field])
            ? call_user_func($mods[$flags], (string)$this->data[$field])
            : null;
    }

    public function number($field)
    {
        return isset($this->data[$field])
            ? (float)strtr($this->data[$field], [' ' => '', /* nbsp */' ' => '',  ',' => '.'])
            : null;
    }

    public function hash($field, $type = 'raw')
    {
        $args = array_merge([$type], array_slice(func_get_args(), 2));

        return isset($this->data[$field]) && is_array($this->data[$field])
            ? array_filter(call_user_func_array([$this, 'castAll'], array_merge([$this->data[$field]], $args)))
            : [];
    }

    public function collection($field, $type = 'raw')
    {
        return call_user_func_array([$this, 'hash'], func_get_args());
    }

    public function enum($field, array $map)
    {
        $val = $this->string($field);

        return null !== $val && isset($map[$val]) ? $map[$val] : null;
    }

    public function raw($field)
    {
        return isset($this->data[$field]) ? $this->data[$field] : null;
    }

    public function data($field)
    {
        return new self($this->hash($field));
    }

    public function filled($field, $requiredFields)
    {
        if ($structData = $this->hash($field)) {
            if (!array_diff_key($structData, array_reverse($requiredFields))) {
                $structData = null;
            }
        }

        return $structData;
    }

    public function set($field, $value)
    {
        $this->data[$field] = $value;
    }

    // -------- structures ---------

    public function phone($field, $defaultType = self::PHONE_TYPE_HOME) {
        static $types = [
            'кон' => self::PHONE_TYPE_MOBILE,
            'моб' => self::PHONE_TYPE_MOBILE,
            'дом' => self::PHONE_TYPE_HOME,
            'раб' => self::PHONE_TYPE_WORK,
            'фак' => self::PHONE_TYPE_FAX,
        ];

        if (null === $phoneData = $this->hash($field)) {
            return null;
        }

        $phoneData = self::create($phoneData);

        $typeCode = mb_strtolower(mb_substr($phoneData->string('Type', self::STR_TOLOWER), 0, 3));

        return [
            'number' => $phoneData->string('Number'),
            'type' => @$types[$typeCode] ?: $defaultType
        ];
    }

    // ------  helpers ----------

    /**
     * @param array $data
     *
     * @return DataArray
     */
    public static function create(array $data)
    {
        return new self($data);
    }

    private function castAll(array $arr, $type)
    {
        $data = new self($arr);
        $args = array_slice(func_get_args(), 2);

        foreach (array_keys($arr) as $idx) {
            $arr[$idx] = call_user_func_array([$data, $type], array_merge([$idx], $args));
        }

        return $arr;
    }
}
