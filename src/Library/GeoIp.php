<?php

/**
 * This class abstracts access to the GeoIP service
 *
 * @package     Nails
 * @subpackage  module-geo-ip
 * @category    Library
 * @author      Nails Dev Team
 * @link
 * @todo        Update this library to be a little more comprehensive, like the CDN library
 */

namespace Nails\GeoIp\Library;

use Nails\GeoIp\Exception\GeoIpException;
use Nails\GeoIp\Exception\GeoIpDriverException;

class GeoIp
{
    use \Nails\Common\Traits\Caching;

    // --------------------------------------------------------------------------

    protected $oDriver;
    protected $aCache;

    // --------------------------------------------------------------------------

    const DEFAULT_DRIVER = 'nailsapp/driver-geo-ip-nails';

    // --------------------------------------------------------------------------

    /**
     * Construct the Library, test that the driver is valid
     * @throws GeoIpException
     */
    public function __construct()
    {
        //  Load the driver
        // @todo: build a settings interface for setting and configuring the driver.
        $sSlug    = defined('APP_GEO_IP_DRIVER') ? strtolower(APP_GEO_IP_DRIVER) : self::DEFAULT_DRIVER;
        $aDrivers = _NAILS_GET_DRIVERS('nailsapp/module-geo-ip');
        $oDriver  = null;

        for ($i=0; $i < count($aDrivers); $i++) {
            if ($aDrivers[$i]->slug == $sSlug) {
                $oDriver = $aDrivers[$i];
                break;
            }
        }

        if (empty($oDriver)) {
            throw new GeoIpDriverException('"' . $sSlug . '" is not a valid Geo-IP driver', 1);
        }

        $sDriverClass = $oDriver->data->namespace . $oDriver->data->class;

        //  Ensure driver implements the correct interface
        $sInterfaceName = 'Nails\GeoIp\Interfaces\Driver';
        if (!in_array($sInterfaceName, class_implements($sDriverClass))) {

            throw new GeoIpDriverException(
                '"' . $sDriverClass . '" must implement ' . $sInterfaceName,
                2
            );
        }

        $this->oDriver = _NAILS_GET_DRIVER_INSTANCE($oDriver);
    }

    // --------------------------------------------------------------------------

    /**
     * Return all information about a given IP
     * @param string $sIp The IP to get details for
     * @return \stdClass
     */
    public function lookup($sIp = '')
    {
        $sIp = trim($sIp);

        if (empty($sIp) && !empty($_SERVER['REMOTE_ADDR'])) {

            $sIp = $_SERVER['REMOTE_ADDR'];
        }

        $oCache = $this->getCache($sIp);

        if (!empty($oCache)) {

            return $oCache;
        }

        $oIp = $this->oDriver->lookup($sIp);

        if (!($oIp instanceof \Nails\GeoIp\Result\Ip)) {

            throw new GeoIpException(
                'Geo IP Driver did not return a \Nails\GeoIp\Result\Ip result',
                3
            );
        }

        $this->setCache($sIp, $oIp);

        return $oIp;
    }

    // --------------------------------------------------------------------------

    /**
     * Return the IP property of a lookup
     * @param string $sIp The IP to look up
     * @return string|null
     */
    public function ip($sIp)
    {
        return $this->lookup($sIp)->getIp();
    }

    // --------------------------------------------------------------------------

    /**
     * Return the hostname property of a lookup
     * @param string $sIp The IP to look up
     * @return string|null
     */
    public function hostname($sIp = '')
    {
        return $this->lookup($sIp)->getHostname();
    }

    // --------------------------------------------------------------------------

    /**
     * Return the city property of a lookup
     * @param string $sIp The IP to look up
     * @return string|null
     */
    public function city($sIp = '')
    {
        return $this->lookup($sIp)->getCity();
    }

    // --------------------------------------------------------------------------

    /**
     * Return the region property of a lookup
     * @param string $sIp The IP to look up
     * @return string|null
     */
    public function region($sIp = '')
    {
        return $this->lookup($sIp)->getRegion();
    }

    // --------------------------------------------------------------------------

    /**
     * Return the country property of a lookup
     * @param string $sIp The IP to look up
     * @return string|null
     */
    public function country($sIp = '')
    {
        return $this->lookup($sIp)->getCountry();
    }

    // --------------------------------------------------------------------------

    /**
     * Return the latLng property of a lookup
     * @param string $sIp The IP to look up
     * @return string|null
     */
    public function latLng($sIp = '')
    {
        return $this->lookup($sIp)->getLatLng();
    }

    // --------------------------------------------------------------------------

    /**
     * Return the lat property of a lookup
     * @param string $sIp The IP to look up
     * @return string|null
     */
    public function lat($sIp = '')
    {
        return $this->lookup($sIp)->getLat();
    }

    // --------------------------------------------------------------------------

    /**
     * Return the lng property of a lookup
     * @param string $sIp The IP to look up
     * @return string|null
     */
    public function lng($sIp = '')
    {
        return $this->lookup($sIp)->getLng();
    }
}
