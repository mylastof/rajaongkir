<?php
namespace Ncaneldiee\Rajaongkir;

use Ncaneldiee\Rajaongkir\Helper\Curl;

class Rajaongkir
{
    /**
     * The name of the basic account.
     *
     * @var string
     */
    const ACCOUNT_BASIC = 'basic';

    /**
     * The name of the pro account.
     *
     * @var string
     */
    const ACCOUNT_PRO = 'pro';

    /**
     * The name of the starter account.
     *
     * @var string
     */
    const ACCOUNT_STARTER = 'starter';

    /**
     * Account type.
     *
     * @var array
     */
    protected $account = [
        'basic',
        'pro',
        'starter',
    ];

    /**
     * Default account type.
     *
     * @var string
     */
    protected $account_type = 'starter';

    /**
     * API key.
     *
     * @var string
     */
    protected $api_key;

    /**
     * API url.
     *
     * @var array
     */
    protected $api_url = [
        'basic' => 'http://rajaongkir.com/api/basic/',
        'pro' => 'http://pro.rajaongkir.com/api/',
        'starter' => 'http://rajaongkir.com/api/starter/',
    ];

    /**
     * Courier list.
     *
     * @var array
     */
    protected $courier = [
        'basic' => [
            'esl' => 'Eka Sari Lorena (ESL)',
            'jne' => 'Jalur Nugraha Ekakurir (JNE)',
            'pcp' => 'Priority Cargo and Package (PCP)',
            'pos' => 'POS Indonesia (POS)',
            'rpx' => 'RPX Holding (RPX)',
            'tiki' => 'Citra Van Titipan Kilat (TIKI)',
        ],
        'pro' => [
            'cahaya' => 'Cahaya Ekspress Logistik (CAHAYA)',
            'esl' => 'Eka Sari Lorena (ESL)',
            'jne' => 'Jalur Nugraha Ekakurir (JNE)',
            'jnt' => 'J&T Express (J&T)',
            'pahala' => 'Pahala Kencana Express (PAHALA)',
            'pandu' => 'Pandu Logistics (PANDU)',
            'pcp' => 'Priority Cargo and Package (PCP)',
            'pos' => 'POS Indonesia (POS)',
            'rpx' => 'RPX Holding (RPX)',
            'sicepat' => 'Sicepat Ekspres (SICEPAT)',
            'tiki' => 'Citra Van Titipan Kilat (TIKI)',
            'wahana' => 'Wahana Prestasi Logistik (WAHANA)',
        ],
        'starter' => [
            'jne' => 'Jalur Nugraha Ekakurir (JNE)',
            'pos' => 'POS Indonesia (POS)',
            'tiki' => 'Citra Van Titipan Kilat (TIKI)',
        ],
    ];

    /**
     * Request storage.
     *
     * @var array
     */
    protected $request;

    /**
     * Response storage.
     *
     * @var array
     */
    protected $response = [];

    /**
     * Constructor.
     *
     * @param  string  $account_key
     * @param  string|null  $account_type
     * @return void
     */
    public function __construct($api_key, $account_type = null)
    {
        $this->api_key = $api_key;

        $this->account_type = is_null($account_type) ? $this->account_type : mb_strtolower($account_type);
    }

    /**
     * Get account type
     *
     * @return string
     */
    public function account()
    {
        return $this->account_type;
    }

    /**
     * Get a list or detail of the city.
     *
     * @param  int|null  $province
     * @param  int|null  $id
     * @return object
     */
    public function city($province = null, $id = null)
    {
        return $this->request('city', [
            'province' => $province,
            'id' => $id,
        ]);
    }

    /**
     * Get shipping cost and delivery time.
     *
     * @param  int|array  $origin
     * @param  int|array  $destination
     * @param  int|array  $shipment
     * @param  string  $courier
     * @return object
     */
    public function cost($origin, $destination, $shipment, $courier)
    {
        $parameter = $this->shipment($shipment);
        $parameter['courier'] = $courier;

        if (is_array($origin) && is_array($destination)) {
            $parameter['origin'] = current($origin);
            $parameter['originType'] = key($origin);
            $parameter['destination'] = current($destination);
            $parameter['destinationType'] = key($destination);
        } else {
            $parameter['origin'] = $origin;
            $parameter['destination'] = $destination;

            if (self::ACCOUNT_PRO === $this->account_type) {
                $parameter['originType'] = 'city';
                $parameter['destinationType'] = 'city';
            }
        }

        $parameter = array_map('mb_strtolower', $parameter);

        return $this->request('cost', $parameter, 'POST');
    }

    /**
     * Get a list or detail of the courier.
     *
     * @param  string|null  $courier
     * @return object
     */
    public function courier($courier = null)
    {
        $data = array_merge(...array_values($this->courier));

        if ($courier) {
            $courier = mb_strtolower($courier);

            return array_key_exists($courier, $data) ? $data[$courier] : $this->courier;
        }

        return (object) $data;
    }

    /**
     * Get rupiah exchange rate.
     *
     * @return object
     */
    public function currency()
    {
        return $this->request('currency');
    }

    /**
     * Get a list or detail of the province.
     *
     * @param  int|null  $id
     * @return object
     */
    public function province($id = null)
    {
        return $this->request('province', [
            'id' => $id,
        ]);
    }

    /**
     * Get a list or detail of the subdistrict.
     *
     * @param  int|null  $city
     * @param  int|null  $id
     * @return object
     */
    public function subdistrict($city, $id = null)
    {
        return $this->request('subdistrict', [
            'city' => $city,
            'id' => $id,
        ]);
    }

    /**
     * Track or find out delivery status.
     *
     * @param  string  $id
     * @param  string  $courier
     * @return object
     */
    public function waybill($id, $courier)
    {
        $courier = mb_strtolower($courier);

        return $this->request('waybill', [
            'waybill' => $id,
            'courier' => $courier,
        ], 'POST');
    }

    /**
     * Create a request and send a response.
     *
     * @param  string  $endpoint
     * @param  array  $parameter
     * @param  string  $method
     * @return object
     */
    protected function request($endpoint, array $parameter = [], $method = 'GET')
    {
        $option = [
            CURLOPT_HTTPHEADER => [
                'key: ' . $this->api_key,
            ],
        ];

        if ('POST' === $method) {
            $this->request = Curl::post($this->api_url[$this->account_type] . $endpoint, $parameter, $option);
        } else {
            $this->request = Curl::get($this->api_url[$this->account_type] . $endpoint, $parameter, $option);
        }

        $this->response = [
            'code' => false,
            'data' => false,
            'error' => false,
        ];

        if (isset($this->request->body)) {
            if (200 === $this->request->body->rajaongkir->status->code) {
                $this->response['code'] = $this->request->body->rajaongkir->status->code;

                if (isset($this->request->body->rajaongkir->results)) {
                    $this->response['data'] = $this->request->body->rajaongkir->results;
                } elseif (isset($this->request->body->rajaongkir->result)) {
                    $this->response['data'] = $this->request->body->rajaongkir->result;
                } else {
                    $this->response['code'] = 400;
                    $this->response['error'] = 'Invalid response. Response tidak ditemukan, harap baca dokumentasi dengan baik.';
                }

                if (isset($this->request->body->rajaongkir->origin_details) && isset($this->request->body->rajaongkir->destination_details)) {
                    $this->response['meta'] = [
                        'origin' => $this->request->body->rajaongkir->origin_details,
                        'destination' => $this->request->body->rajaongkir->destination_details,
                        'weight' => $this->request->body->rajaongkir->query->weight,
                        'courier' => $this->request->body->rajaongkir->query->courier,
                    ];
                }
            } else {
                $this->response['code'] = $this->request->body->rajaongkir->status->code;
                $this->response['error'] = $this->request->body->rajaongkir->status->description;
            }
        } else {
            $this->response['code'] = 400;
            $this->response['error'] = 'Invalid response. Response tidak ditemukan, harap baca dokumentasi dengan baik.';
        }

        return (object) $this->response;
    }

    /**
     * Shipment detail.
     *
     * @param  string|array  $shipment
     * @return array
     */
    protected function shipment($shipment)
    {
        $data = [];

        if (is_array($shipment)) {
            if (isset($shipment['length']) && isset($shipment['width']) && isset($shipment['height'])) {
                $volumetric = (($shipment['length'] * $shipment['width'] * $shipment['height']) / 6000) * 1000;
                $actual = isset($shipment['weight']) ? $shipment['weight'] : 0;

                $data['weight'] = $volumetric > $actual ? $volumetric : $actual;
                $data['length'] = $shipment['length'];
                $data['width'] = $shipment['width'];
                $data['height'] = $shipment['height'];
            }
        } else {
            $data['weight'] = $shipment;
        }

        return $data;
    }
}
