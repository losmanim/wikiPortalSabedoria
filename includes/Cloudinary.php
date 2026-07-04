<?php

class Cloudinary {
    private static $instancia = null;
    private $cloudName;
    private $apiKey;
    private $apiSecret;

    private function __construct() {
        $this->cloudName = defined('CLOUDINARY_CLOUD_NAME') ? CLOUDINARY_CLOUD_NAME : getenv('CLOUDINARY_CLOUD_NAME');
        $this->apiKey = defined('CLOUDINARY_API_KEY') ? CLOUDINARY_API_KEY : getenv('CLOUDINARY_API_KEY');
        $this->apiSecret = defined('CLOUDINARY_API_SECRET') ? CLOUDINARY_API_SECRET : getenv('CLOUDINARY_API_SECRET');
    }

    public static function getInstance() {
        if (self::$instancia === null) {
            self::$instancia = new self();
        }
        return self::$instancia;
    }

    public function listResources($folder = null, $maxResults = 100) {
        $expression = $folder ? "folder:$folder" : "resource_type:image";
        $url = "https://api.cloudinary.com/v1_1/{$this->cloudName}/resources/search";
        $auth = base64_encode("{$this->apiKey}:{$this->apiSecret}");

        $body = json_encode([
            'expression' => $expression,
            'max_results' => $maxResults,
                    'sort_by' => [['created_at' => 'desc']]
        ]);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json', "Authorization: Basic {$auth}"],
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30
        ]);
        $result = json_decode(curl_exec($ch), true);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            error_log("Cloudinary API error ($httpCode): " . ($result['error']['message'] ?? 'unknown'));
            return [];
        }

        return $result['resources'] ?? [];
    }

    public function listFolders() {
        $url = "https://api.cloudinary.com/v1_1/{$this->cloudName}/folders";
        $auth = base64_encode("{$this->apiKey}:{$this->apiSecret}");

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_HTTPGET => true,
            CURLOPT_HTTPHEADER => ["Authorization: Basic {$auth}"],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15
        ]);
        $result = json_decode(curl_exec($ch), true);
        curl_close($ch);

        return $result['folders'] ?? [];
    }

    public function upload($filePath, $publicId = null, $resourceType = 'image') {
        $url = "https://api.cloudinary.com/v1_1/{$this->cloudName}/{$resourceType}/upload";
        $auth = base64_encode("{$this->apiKey}:{$this->apiSecret}");

        $data = ['file' => new CURLFile($filePath)];
        if ($publicId) {
            $data['public_id'] = $publicId;
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ["Authorization: Basic {$auth}"],
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 60
        ]);
        $result = json_decode(curl_exec($ch), true);
        curl_close($ch);

        return $result;
    }

    public function delete($publicId) {
        $url = "https://api.cloudinary.com/v1_1/{$this->cloudName}/resources/image/upload";
        $auth = base64_encode("{$this->apiKey}:{$this->apiSecret}");

        $body = json_encode(['public_ids' => [$publicId]]);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST => 'DELETE',
            CURLOPT_HTTPHEADER => ['Content-Type: application/json', "Authorization: Basic {$auth}"],
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15
        ]);
        $result = json_decode(curl_exec($ch), true);
        curl_close($ch);

        return $result;
    }

    public function getUrl($resource) {
        return $resource['secure_url'] ?? null;
    }

    public function getResourceType($resource) {
        return $resource['resource_type'] ?? 'image';
    }

    public function getPublicId($resource) {
        return $resource['public_id'] ?? '';
    }

    public function getFolderDisplayName($folderName) {
        $parts = explode('/', $folderName);
        $last = end($parts);
        return preg_replace('/^\d+_/', '', $last);
    }
}
