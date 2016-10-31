<?php
namespace Ncaneldiee\Rajaongkir;

class RajaongkirInternational extends Rajaongkir
{
    /**
     * Default account type.
     *
     * @var string
     */
    protected $account_type = 'basic';

    /**
     * API version.
     *
     * @var string
     */
    protected $api_version = 'v2';

    /**
     * Courier list.
     *
     * @var array
     */
    protected $courier = [
        'basic' => [],
        'pro' => [
            'pos' => 'POS Indonesia (POS)',
            'tiki' => 'Citra Van Titipan Kilat (TIKI)',
        ],
        'starter' => [
            'pos' => 'POS Indonesia (POS)',
            'tiki' => 'Citra Van Titipan Kilat (TIKI)',
        ],
    ];

    /**
     * Constructor.
     *
     * @param  string  $account_key
     * @param  string|null  $account_type
     * @return void
     */
    public function __construct($account_key, $account_type = null)
    {
        parent::__construct($account_key, $account_type);
    }

    /**
     * Get shipping cost and delivery time.
     *
     * @param  int  $origin
     * @param  int  $destination
     * @param  int|array  $shipment
     * @param  string  $courier
     * @return object
     */
    public function cost($origin, $destination, $shipment, $courier)
    {
        $courier = mb_strtolower($courier);

        $parameter = $this->shipment($shipment);
        $parameter['origin'] = $origin;
        $parameter['destination'] = $destination;
        $parameter['courier'] = $courier;

        return $this->request($this->api_version . '/internationalCost', $parameter, 'POST');
    }

    /**
     * Get a list or detail of the destination country.
     *
     * @param  int|null  $id
     * @return object
     */
    public function destination($id = null)
    {
        return $this->request($this->api_version . '/internationalDestination', [
            'id' => $id,
        ]);
    }

    /**
     * Get a list or detail of the origin city.
     *
     * @param  int|null  $province
     * @param  int|null  $id
     * @return object
     */
    public function origin($province = null, $id = null)
    {
        return $this->request($this->api_version . '/internationalOrigin', [
            'province' => $province,
            'id' => $id,
        ]);
    }
}
