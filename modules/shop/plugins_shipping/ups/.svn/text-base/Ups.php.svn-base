<?php
/**
 * File containing the classes used for integrating UPS shipping with XE Shop
 */
/**
 * Shipping plugin class for adding UPS as a shipping method in XE Shop
 *
 * @author Corina Udrescu (corina.udrescu@arnia.ro)
 */
class Ups extends ShippingMethodAbstract
{
	const SANDBOX_URL = 'https://wwwcie.ups.com/ups.app/xml/Rate'
		,LIVE_URL = 'https://onlinetools.ups.com/ups.app/xml/Rate';

	const PICKUP_TYPE_DAILY_PICKUP = '01'
		, PICKUP_TYPE_CUSTOMER_COUNTER = '03'
		, PICKUP_TYPE_ONE_TIME_PICKUP = '06'
		, PICKUP_TYPE_ON_CALL_AIR = '07'
		, PICKUP_TYPE_LETTER_CENTER = '19'
		, PICKUP_TYPE_AIR_SERVICE_CENTER = '20';

	// Domestic
	const SERVICE_NEXT_DAY_AIR_EARLY_AM = '14'
		, SERVICE_NEXT_DAY_AIR = '01'
		, SERVICE_NEXT_DAY_AIR_SAVER = '13'
		, SERVICE_2ND_DAY_AIR_AM = '59'
		, SERVICE_2ND_DAY_AIR = '02'
		, SERVICE_3DAY_SELECT = '12'
		, SERVICE_GROUND = '03';

	// International
	const SERVICE_STANDARD = '11'
		, SERVICE_WORLDWIDE_EXPRESS = '07'
		, SERVICE_WORLDWIDE_EXPRESS_PLUS = '54'
		, SERVICE_WORLDWIDE_EXPEDITED = '08'
		, SERVICE_SAVER = '65';

	public static $service_names = array(
		Ups::SERVICE_NEXT_DAY_AIR_EARLY_AM => 'Domestic - Next Day Air Early AM'
		, Ups::SERVICE_NEXT_DAY_AIR => 'Domestic - Next Day Air'
		, Ups::SERVICE_NEXT_DAY_AIR_SAVER => 'Domestic - Next Day Air Saver'
		, Ups::SERVICE_2ND_DAY_AIR_AM => 'Domestic - 2nd Day Air AM'
		, Ups::SERVICE_2ND_DAY_AIR => 'Domestic - 2nd Day Air'
		, Ups::SERVICE_3DAY_SELECT => 'Domestic - 3 Day Select'
		, Ups::SERVICE_GROUND => 'Domestic - Ground'
		, Ups::SERVICE_STANDARD => 'International - Standard'
		, Ups::SERVICE_WORLDWIDE_EXPRESS => 'International - Worldwide Express'
		, Ups::SERVICE_WORLDWIDE_EXPRESS_PLUS => 'International - Worldwide Epxress Plus'
		, Ups::SERVICE_WORLDWIDE_EXPEDITED => 'International - Worldwide Expedited'
		, Ups::SERVICE_SAVER => 'International - Saver'
	);

	const PACKAGE_TYPE_UNKNOWN = '00'
		, PACKAGE_TYPE_UPS_LETTER = '01'
		, PACKAGE_TYPE_PACKAGE = '02'
		, PACKAGE_TYPE_TUBE = '03'
		, PACKAGE_TYPE_PAK = '04'
		, PACKAGE_TYPE_EXPRESS_BOX = '21'
		, PACKAGE_TYPE_25KG_BOX = '24'
		, PACKAGE_TYPE_15KG_BOX = '25'
		, PACKAGE_TYPE_PALLET = '30';

	/**
	 * Checks is custom plugin parameters are set and valid;
	 * If no validation is needed, just return true;
	 *
	 * @param string $error_message
	 * @return mixed
	 */
	public function isConfigured(&$error_message = 'msg_invalid_request')
	{
		if($error_message == '') $error_message = 'msg_invalid_request';
		if(!isset($this->gateway_api)) return FALSE;
		if(!isset($this->username)) return FALSE;
		if(!isset($this->password)) return FALSE;
		if(!isset($this->shipper_number)) return FALSE;
		if(!isset($this->api_access_key)) return FALSE;
		return TRUE;
	}

	/**
	 * Calculates shipping rates
	 *
	 * @param Cart    $cart             SHipping cart for which to calculate shipping
	 * @param string $service			Service for which to calculate cost (Standard, Priority etc.)
	 * @return float|int
	 */
	public function calculateShipping(Cart $cart, $service = NULL)
	{
		if($cart->getShippingAddress() == NULL) return 0;

		$ups_api = new UpsAPI($this);
		try
		{
			$shipping_cost = $ups_api->getRate($cart, $service);
		}
		catch(APIException $exception)
		{
			$shipping_cost = 0; // TODO Here maybe return something other than 0
		}

		return $shipping_cost;
	}

	/**
	 * Defines the variants of a certain shipping type
	 * For instance, for UPS we can have: Expedited, Saver etc.
	 */
	public function getVariants()
	{
		$enabled_services = $this->enabled_services;
		if(!$enabled_services) return array();

		$variants = array();
		foreach($enabled_services as  $enabled_service)
		{
			$variants[$enabled_service] = Ups::$service_names[$enabled_service];
		}
		return $variants;
	}

	/**
	 * Checks to see if shipping method has more types
	 * For instance, for UPS we can have: Expedited, Saver etc.
	 */
	public function hasVariants()
	{
		return TRUE;
	}


	/**
	 * Returns a list of available variants
	 * The structure is:
	 * array(
	 *     stdclass(
	 *         'name' => 'ups'
	 *         , 'display_name' => 'UPS'
	 *         , 'variant' => '01'
	 *         , 'variant_display_name' => 'Domestic'
	 *         ,  price => 12
	 * ))
	 *
	 * @param Cart $cart
	 * @internal param \Address $shipping_address
	 * @return array
	 */
	public function getAvailableVariants(Cart $cart)
	{
		$shipping_address = $cart->getShippingAddress();
		if(!$shipping_address)
		{
			$variant = new stdClass();
			$variant->name = $this->getName();
			$variant->display_name = $this->getDisplayName();
			$variant->variant = NULL;
			$variant->variant_display_name = 'Rates and available UPS services will show up after you enter your shipping address.';
			$variant->price = NULL;
			return array($variant);
		}

		$ups_api = new UpsAPI($this);
		try
		{
			$available_rates = $ups_api->getAvailableRates($cart);
		}
		catch(APIException $exception)
		{
			$available_rates = array();
		}


		$available_variants = array();
		foreach($available_rates as $rate)
		{
			$service_code = $rate['service'];
			$price = $rate['price'];
			// Even though UPS supports a certain service for the current Cart,
			// if the admin didn't enable it, we skip it
			if(!in_array($service_code, $this->enabled_services)) continue;
			$variant = new stdClass();
			$variant->name = $this->getName();
			$variant->display_name = $this->getDisplayName();
			$variant->variant = $service_code;
			$variant->variant_display_name = UPS::$service_names[$service_code];
			$variant->price = $price;
			$available_variants[] = $variant;
		}
		return $available_variants;
	}
}

/**
 * Wraps the UPS API calls
 *
 * @author Corina Udrescu (corina.udrescu@arnia.ro)
 */
class UpsAPI extends APIAbstract
{
	private $ups_config;

	/**
	 * Constructor
	 *
	 * @param Ups $ups_config
	 */
	public function __construct(Ups $ups_config)
	{
		$this->ups_config = $ups_config;
	}

	/**
	 * Calls the Shop method of the UPS API
	 * for a given shipping address
	 *
	 * @param Cart $cart
	 * @return array
	 * @throws APIException
	 */
	public function getAvailableRates(Cart $cart)
	{
		$data = $this->getAccessRequestXML(
		  	$this->ups_config->api_access_key
			, $this->ups_config->username
			, $this->ups_config->password
		);
		$data .= $this->getRatingServiceSelectionRequestXML(
			$this->ups_config
			, $cart
			, 'Shop'
		);

		if($this->ups_config->gateway_api == Ups::SANDBOX_URL)
		{
			// If using sandbox, skip SSL verify, otherwise all requests fail with:
			// "Error [60]: SSL certificate problem, verify that the CA cert is OK"
			$response = $this->request($this->ups_config->gateway_api, $data, TRUE);
		}
		else
		{
			$response = $this->request($this->ups_config->gateway_api, $data);
		}

		$RatingServiceSelectionResponse = simplexml_load_string($response);

		if(intval(strip_tags($RatingServiceSelectionResponse->Response->ResponseStatusCode->asXml())) === 0)
		{
			$error = $RatingServiceSelectionResponse->Response->Error;
			throw new APIException("UPS Error: [ErrorSeverity] - " . $error->ErrorSeverity . "; [ErrorCode] - " . $error->ErrorCode . '; [Error description] - ' . $error->ErrorDescription);
		}

		$available_rates = array();
		foreach($RatingServiceSelectionResponse->RatedShipment as $RatedShipment)
		{
			$shipping_service = strip_tags($RatedShipment->Service->Code->asXml());
			$shipping_cost = floatval(strip_tags($RatedShipment->TotalCharges->MonetaryValue->asXml()));
			$available_rates[] = array('service' => $shipping_service, 'price' => $shipping_cost);
		}
		return $available_rates;
	}

	/**
	 * Calls the Rate method of the UPS API
	 *
	 * @param Cart $cart
	 * @param      $service
	 * @return float|int
	 * @throws APIException
	 */
	public function getRate(Cart $cart, $service)
	{
		if(!$service) return 0; // TODO See how to treat this - giving shipping for free is not necessarily the best idea :)
		// Should throw AddressNotAvailable exception so I can show a message

		$data = $this->getAccessRequestXML(
			$this->ups_config->api_access_key
			, $this->ups_config->username
			, $this->ups_config->password
		);
		$data .= $this->getRatingServiceSelectionRequestXML(
			$this->ups_config
			, $cart
			, 'Rate'
			, $service
		);

		if($this->ups_config->gateway_api == Ups::SANDBOX_URL)
		{
			// If using sandbox, skip SSL verify, otherwise all requests fail with:
			// "Error [60]: SSL certificate problem, verify that the CA cert is OK"
			$response = $this->request($this->ups_config->gateway_api, $data, TRUE);
		}
		else
		{
			$response = $this->request($this->ups_config->gateway_api, $data);
		}

		$RatingServiceSelectionResponse = simplexml_load_string($response);

		if(intval(strip_tags($RatingServiceSelectionResponse->Response->ResponseStatusCode->asXml())) === 0)
		{
			$error = $RatingServiceSelectionResponse->Response->Error;
			throw new APIException("UPS Error: [ErrorSeverity] - " . $error->ErrorSeverity . "; [ErrorCode] - " . $error->ErrorCode . '; [Error description] - ' . $error->ErrorDescription);
		}
		$shipping_cost = floatval(strip_tags($RatingServiceSelectionResponse->RatedShipment->TotalCharges->MonetaryValue->asXml()));
		return $shipping_cost;
	}

	/**
	 * Returns the XML for authenticating the request
	 *
	 * @param $access_license_number
	 * @param $userid
	 * @param $userpassword
	 * @return string
	 */
	public function getAccessRequestXML($access_license_number, $userid, $userpassword)
	{
		return "<?xml version=\"1.0\" ?>
			<AccessRequest xml:lang='en-US'>
				<AccessLicenseNumber>
					$access_license_number
				</AccessLicenseNumber>
				<UserId>
					$userid
				</UserId>
				<Password>
					$userpassword
				</Password>
			</AccessRequest>";
	}

	/**
	 * Generates the XML needed for the making a request to the Rating API
	 *
	 * @param      $ups_config
	 * @param Cart $cart
	 * @param      $RequestOption
	 * @param null $service
	 * @return string
	 */
	public function getRatingServiceSelectionRequestXML($ups_config, Cart $cart, $RequestOption /* Rate or Shop */, $service = NULL)
	{
		$shipping_address = $cart->getShippingAddress();
		$request = "<?xml version=\"1.0\" ?>
			<RatingServiceSelectionRequest>
				<Request>
					<TransactionReference>
						<CustomerContext>Rating and Service</CustomerContext>
						<XpciVersion>1.0</XpciVersion>
					</TransactionReference>
					<RequestAction>Rate</RequestAction>
			        <RequestOption>$RequestOption</RequestOption>
				</Request>
				<PickupType>
					<Code>$ups_config->pickup_type</Code>
				</PickupType>
				<Shipment>
					<Shipper>
						<ShipperNumber>$ups_config->shipper_number</ShipperNumber>
						<Address>
							<PostalCode>$ups_config->postal_code</PostalCode>
							<CountryCode>$ups_config->country_code</CountryCode>
						</Address>
					</Shipper>
					<ShipFrom>
						<Address>
							<PostalCode>$ups_config->postal_code</PostalCode>
							<CountryCode>$ups_config->country_code</CountryCode>
						</Address>
					</ShipFrom>
					<ShipTo>
						<!--<CompanyName>$shipping_address->company</CompanyName>
						<AttentionName>$shipping_address->firstname $shipping_address->lastname</AttentionName>
						<PhoneNumber>$shipping_address->telephone</PhoneNumber>-->
						<Address>
							<!-- <AddressLine1>$shipping_address->address</AddressLine1>
							<City>$shipping_address->city</City> -->
							<PostalCode>$shipping_address->postal_code</PostalCode>
							<CountryCode>$shipping_address->country</CountryCode>
						</Address>
					</ShipTo>";
			$request .= ( $service ? "<Service><Code>$service</Code></Service>" : '');
			$request .=	"<Package>
						<PackagingType>
							<Code>$ups_config->package_type</Code>
						</PackagingType>
						<!-- Dimensions are required if Packaging Type if not Letter, Express Tube,
						or Express Box; Required for GB to GB and Poland to Poland shipments -->
						<!-- <Dimensions>
							<UnitOfMeasurement>
								<Code>CM</Code>
							</UnitOfMeasurement>
							<Length>$length</Length>
							<Width>$width</Width>
							<Height>$height</Height>
						</Dimensions>-->
						<!-- Weight allowed for letters/envelopes.-->
						<PackageWeight>
    						<UnitOfMeasurement>";

			$request .= "<Code>" . ($cart->getUnitOfMeasure() == ShopInfo::UNIT_OF_MEASURE_KGS ? "KGS" : "LBS") . "</Code>";


			$cart_weight = $cart->getTotalWeight();
    		$request .=			"</UnitOfMeasurement>
    						<Weight>$cart_weight</Weight>
    					</PackageWeight>
					</Package>
					<ShipmentServiceOptions />
				</Shipment>
			</RatingServiceSelectionRequest>";
		return $request;
	}


}