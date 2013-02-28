<?php
/**
 * File containing the APIAbstract class
 */

/**
 * Base class for creating API calls
 *
 * Contains the code for sending request to an API endpoint;
 * Returns the response body
 *
 * @author Corina Udrescu (corina.udrescu@arnia.ro)
 */
abstract class APIAbstract
{
	/**
	 * Hides away the curl request
	 *
	 * @param      $url     API Endpoint to send request to
	 * @param      $data    Request parameters - sent as POST
	 * @param bool $skip_ssl_verify Skip SSL verification - useful for development (and local testing)
	 * @return mixed
	 * @throws APIException
	 */
	public function request($url, $data, $skip_ssl_verify = FALSE)
    {
		if(is_string($data))
		{
			$post_string = $data;
		}
		else
		{
			$post_string = http_build_query($data);
		}
        if(__DEBUG__)
        {
            ShopLogger::log('REQUEST ' . $url . ' ' . $post_string);
        }

        // Request
        $request = curl_init($url);
        curl_setopt($request, CURLOPT_HEADER, 0);
        curl_setopt($request, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($request, CURLOPT_POSTFIELDS, $post_string);

		if($skip_ssl_verify)
		{
			curl_setopt($request, CURLOPT_SSL_VERIFYPEER, FALSE);
		}
		else
		{
			curl_setopt($request, CURLOPT_SSL_VERIFYPEER, TRUE);
		}

        $response = curl_exec($request);
		if(!$response)
		{
			$error = curl_error($request);
			throw new APIException("CURL error: " . $error);
		}
        if(__DEBUG__)
        {
            ShopLogger::log('RESPONSE ' . $response);
        }

        curl_close ($request);
        return $response;
    }
}