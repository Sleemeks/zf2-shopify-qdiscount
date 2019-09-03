<?php

namespace ShopifyApi;

class Resources
{
	
	public function shopify_install($token, $shop, $api_endpoint, $query = array(), $method = 'GET', $request_headers = array())
	{
		$url = "https://" . $shop . $api_endpoint;
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_HEADER, true);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
		
		$request_headers[] = "";
		if (!is_null($token)) $request_headers[] = "X-Shopify-Access-Token: " . $token;
		curl_setopt($curl, CURLOPT_HTTPHEADER, $request_headers);

		if ($method != 'GET' && in_array($method, array('POST', 'PUT'))) {
			if (is_array($query)) $query = http_build_query($query);
			curl_setopt ($curl, CURLOPT_POSTFIELDS, $query);
		}
		
		$response = curl_exec($curl);
		curl_close($curl);

		return $this->responseHeaders($response);
	}
	

	
    public function shopify_call($token, $shop, $api_endpoint, $query = array(), $method = 'GET', $request_headers = array())
    {
        $url = "https://" . $shop . $api_endpoint;
        if (!is_null($query) && in_array($method, array('GET', 	'DELETE'))) $url = $url . "?" . http_build_query($query);

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HEADER, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);

        if (!is_null($token))
			curl_setopt($curl, CURLOPT_HTTPHEADER, array("X-Shopify-Access-Token: " . $token, 'Content-Type: application/json'));

        if ($method != 'GET' && in_array($method, array('POST', 'PUT'))) {
				$query = json_encode($query);
                if (is_array($query)) $query = http_build_query($query);
                curl_setopt ($curl, CURLOPT_POSTFIELDS, $query);
        }

        $response = curl_exec($curl);
        curl_close($curl);

        return $this->responseHeaders($response);
    }

    public function shopify_call_webhook($token, $shop, $api_endpoint, $query = array(), $method = 'GET')
    {
        $url = "https://" . $shop . $api_endpoint;
        if (!is_null($query) && in_array($method, array('GET', 	'DELETE'))) $url = $url . "?" . http_build_query($query);

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HEADER, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);

        if (!is_null($token)){
			curl_setopt($curl, CURLOPT_HTTPHEADER, array("X-Shopify-Access-Token: " . $token,'Content-Type: application/json', 'HTTP_X_SHOPIFY_HMAC_SHA256'));
        }

        if ($method != 'GET' && in_array($method, array('POST', 'PUT'))) {
                if (is_array($query)) $query = http_build_query($query);
                curl_setopt ($curl, CURLOPT_POSTFIELDS, $query);
        }

        $response = curl_exec($curl);
        curl_close($curl);

        return $this->responseHeaders($response);
    }

    public function responseHeaders($response)
    {
        $resp = preg_split("/\r\n\r\n|\n\n|\r\r/", $response, 2);
        $headers = array();
        $header_data = explode("\n",$resp[0]);
        $headers['status'] = $header_data[0];
        array_shift($header_data);
        foreach($header_data as $part) {
                $h = explode(":", $part);
                $headers[trim($h[0])] = trim($h[1]);
        }

        return array('headers' => $headers, 'response' => $resp[1]);
    }
}