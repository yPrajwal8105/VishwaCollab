<?php
// Helper to fetch live jobs from the official Adzuna Jobs API.
// Make sure you keep your keys private and do not commit them to any public repo.

function fetch_adzuna_jobs($keyword = '', $location = '', $results = 10)
{
    // Your Adzuna credentials (from https://developer.adzuna.com/)
    // Prefer environment variables so deployments/local setups can override safely.
    $appId  = getenv('ADZUNA_APP_ID') ?: 'eb2290f8'; // APP_ID
    $appKey = getenv('ADZUNA_APP_KEY') ?: '5cb739f3d1dbc32151095cbcf040dc25'; // APP_KEY
    $country = 'in'; // country code (e.g. in, gb, us)

    if (!$appId || !$appKey) {
        $GLOBALS['ADZUNA_LAST_ERROR'] = 'Adzuna credentials are missing.';
        return [];
    }

    $page = 1;
    $baseUrl = "https://api.adzuna.com/v1/api/jobs/{$country}/search/{$page}";

    $params = [
        'app_id'  => $appId,
        'app_key' => $appKey,
        'results_per_page' => max(1, (int)$results),
        'content-type' => 'application/json',
    ];
    
    // Only add 'what' parameter if keyword is provided
    if (!empty($keyword)) {
        $params['what'] = $keyword;
    }
    
    // Only add 'where' parameter if location is provided
    if (!empty($location)) {
        $params['where'] = $location;
    }

    $url = $baseUrl . '?' . http_build_query($params);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_FOLLOWLOCATION => true,
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($response === false || !empty($curlError)) {
        error_log("Adzuna API cURL Error: " . $curlError);
        $GLOBALS['ADZUNA_LAST_ERROR'] = 'Unable to reach Adzuna API (network error).';
        return [];
    }

    if ($httpCode !== 200) {
        error_log("Adzuna API HTTP Error: " . $httpCode);
        $GLOBALS['ADZUNA_LAST_ERROR'] = 'Adzuna API error (HTTP ' . $httpCode . ').';
        return [];
    }

    $data = json_decode($response, true);
    
    // Check for API errors
    if (isset($data['error'])) {
        error_log("Adzuna API Error: " . json_encode($data['error']));
        $GLOBALS['ADZUNA_LAST_ERROR'] = 'Adzuna API returned an error.';
        return [];
    }
    
    if (!isset($data['results']) || !is_array($data['results'])) {
        // If no results but API call succeeded, return empty array
        $GLOBALS['ADZUNA_LAST_ERROR'] = '';
        return [];
    }

    $jobs = [];
    foreach ($data['results'] as $item) {
        $jobs[] = [
            'title'   => $item['title'] ?? '',
            'company' => $item['company']['display_name'] ?? '',
            'location'=> $item['location']['display_name'] ?? '',
            'url'     => $item['redirect_url'] ?? '',
            'snippet' => $item['description'] ?? '',
        ];
    }

    return $jobs;
}


