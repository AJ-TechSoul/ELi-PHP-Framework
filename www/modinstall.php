<?php

CLASS PWA {  

 private $url;

  public function __construct(string $url) {
    $this->url = rtrim($url, '/');
  }

  public function checkPWA(): array {
    $checks = [
      'HTTPS' => $this->checkHttps(),
      'MANIFEST' => $this->checkManifest(),
      'SERVICE_WORKER' => $this->checkServiceWorker(),
      'SPLASH_SCREEN' => $this->checkSplashScreen(),
      'OFFLINE' => $this->checkOffline(),
    ];

    return $checks;
  }

  private function checkHttps(): bool {
    return parse_url($this->url, PHP_URL_SCHEME) === 'https';
  }

  private function checkManifest(): bool {
    $manifestPath = $this->url . '/manifest.json';
    $manifest = @file_get_contents($manifestPath);

    return !empty($manifest);
  }

  private function checkServiceWorker(): bool {
    $serviceWorkerPath = $this->url . '/service-worker.js';
    $serviceWorker = @file_get_contents($serviceWorkerPath);

    return !empty($serviceWorker);
  }

  private function checkSplashScreen(): bool {
    // Check if there is a splash screen image
    $splashScreenImagePath = $this->url . '/splash.png';
    $splashScreenImage = @file_get_contents($splashScreenImagePath);

    return !empty($splashScreenImage);
  }

  private function checkOffline(): bool {
    // Check if there is a service worker and a manifest
    return $this->checkServiceWorker() && $this->checkManifest();
  }
}	


//  Example
$pwa = new PWA('https://techsoul.in');
$checks = $pwa->checkPWA();

foreach ($checks as $name => $result) {
  if ($result) {
    echo "{$name}: <span style='color: green;'>✓</span>\n";
  } else {
    echo "{$name}: <span style='color: red;'>✗</span>\n";
  }
}
?>		