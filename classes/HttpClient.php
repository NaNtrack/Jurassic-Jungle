<?php

/**
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 1,2,3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * HttpClient
 *
 * @author jaraya
 */
class HttpClient {
	const METHOD_POST = 'POST';
	const METHOD_GET = 'GET';

	/**
	 *
	 * @var string
	 */
	public $url;

	/**
	 *
	 * @var type
	 */
	private $method;

	/**
	 *
	 * @var array
	 */
	private $params;

	/**
	 *
	 * @var array
	 */
	private $headers;
	
	/**
	 *
	 * @var array
	 */
	private $cookies;

	/**
	 *
	 * @var string
	 */
	private $response;
	public $response_header;
	public $response_body;
	public $response_code;

	public function __construct($url = null, $method = self::METHOD_GET, $params = null) {
		$this->url = $url;
		$this->method = $method;
		$this->params = $params;
		if ($params != null) {
			$this->setParams($params);
		}
	}

	/**
	 * Sets the parameters for the http request
	 *
	 * @method setParams
	 * @param array $params
	 */
	public function setParams($params) {
		if (is_array($params)) {
			$this->params = http_build_query($params, '', '&');
		} else {
			$this->params = null;
		}
	}
	
	
	public function setCookie ($key, $val) {
		$this->cookies[$key] = $val;
	}

	/**
	 * Sets the headers for the http request
	 *
	 * @method setHeaders
	 * @param array $headers
	 */
	public function setHeaders($headers) {
		if (is_array($headers)) {
			$this->headers = $headers;
		}
	}

	/**
	 * Performs a GET HTTP Request
	 *
	 * @param string $url The url
	 * @param array $params The request parameters
	 * @return string
	 */
	public function doGetRequest($url = null, $params = null) {
		if ($url != null) {
			$this->url = $url;
		}
		if ($params != null) {
			$this->setParams($params);
		}
		$this->method = HttpClient::METHOD_GET;
		return $this->doRequest();
	}

	/**
	 * Performs a POST HTTP Request
	 *
	 * @param string $url The url
	 * @param array $params The request parameters
	 * @return string
	 */
	public function doPostRequest($url = null, $params = null) {
		if ($url != null) {
			$this->url = $url;
		}
		if ($params != null) {
			$this->setParams($params);
		}
		$this->method = HttpClient::METHOD_POST;
		return $this->doRequest();
	}

	public function toURL() {
		return $this->url . '?' . $this->params;
	}

	private function doRequest() {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->url);
		curl_setopt($ch, CURLOPT_VERBOSE, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

		if ($this->method == HttpClient::METHOD_POST) {
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $this->params);
		} elseif ($this->method == HttpClient::METHOD_GET) {
			curl_setopt($ch, CURLOPT_URL, $this->url);
		}

		if ($this->headers) {
			curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
		}
		
		if ( $this->cookies) {
			$cookie = '';
			foreach ($this->cookies as $k => $v ) {
				$cookie .= "$k=$v; ";
			}
			curl_setopt($ch, CURLOPT_COOKIE, $cookie);
			//Log::getInstance()->log("Cookie used : $cookie");
		}

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, true);

		$this->response = curl_exec($ch);

		if (curl_errno($ch)) {
			throw new Exception("Error no : " . curl_errno($ch) . "\nError : " . curl_error($ch));
		} else {
			$curl_info = curl_getinfo($ch);
			$this->response_header = substr($this->response, 0, $curl_info['header_size']);
			$this->response_body = substr($this->response, $curl_info['header_size']);
			$this->response_code = $curl_info['http_code'];
		}

		curl_close($ch);
		
		if ($this->response_code == 400) {
			echo $this->response_body;
			$msg_str = $this->response_header;
			throw new Exception($msg_str, $this->response_code);
		} elseif ($this->response_code != 200) {
			$msg_str = $this->response_header;
			if (false)
				$msg_str .= "\n" . $this->response_body;
			throw new Exception($msg_str, $this->response_code);
		}

		return $this->response_body;
	}

}
