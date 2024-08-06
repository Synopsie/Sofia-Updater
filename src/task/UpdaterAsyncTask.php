<?php

/*
 *  ____   __   __  _   _    ___    ____    ____    ___   _____
 * / ___|  \ \ / / | \ | |  / _ \  |  _ \  / ___|  |_ _| | ____|
 * \___ \   \ V /  |  \| | | | | | | |_) | \___ \   | |  |  _|
 *  ___) |   | |   | |\  | | |_| | |  __/   ___) |  | |  | |___
 * |____/    |_|   |_| \_|  \___/  |_|     |____/  |___| |_____|
 *
 * Sofia-Updater est une api permettant à un plugin de vérifier si une nouvelle release est disponible.
 *
 * @author Synopsie
 * @link https://github.com/Synopsie
 * @version 1.2.0
 *
 */

declare(strict_types=1);

namespace sofia\task;

use pocketmine\scheduler\AsyncTask;
use pocketmine\utils\Internet;
use pocketmine\utils\Terminal;

use function is_array;
use function is_null;
use function json_decode;
use function sprintf;
use function str_replace;
use function version_compare;
use function vsprintf;

class UpdaterAsyncTask extends AsyncTask {
	private const GITHUB_RELEASES_URL = "https://api.github.com/repos/%s/%s/releases";

	public function __construct(
		private readonly string $pluginName,
		private readonly string $pluginVersion,
		private readonly string $githubOwner,
		private readonly string $githubRepo,
		private readonly ?string $githubToken
	) {
	}

	public function onRun() : void {
		$url     = sprintf(self::GITHUB_RELEASES_URL, $this->githubOwner, $this->githubRepo);
		$headers = ['User-Agent: Sofia-Updater'];
		if (!is_null($this->githubToken)) {
			$headers[] = 'Authorization: Bearer ' . $this->githubToken;
		}
		$response       = Internet::getURL($url, 10, $headers, $err);
		$highestVersion = $this->pluginVersion;
		$artifactUrl    = "";
		$api            = "";
		$error          = null;

		if ($response !== null) {
			$status = $response->getCode();
			if ($status !== 200) {
				$error = "Failed to fetch releases. HTTP Status Code: $status";
			} else {
				$responseBody = $response->getBody();
				$releases     = json_decode($responseBody, true);
				if (is_array($releases)) {
					foreach ($releases as $release) {
						$version = str_replace("v", "", $release["tag_name"]);
						if (version_compare($highestVersion, $version, ">=")) {
							continue;
						}
						$highestVersion = $version;
						$artifactUrl    = $release["html_url"];
						$api            = $this->pluginName;
					}
				} else {
					$error = "Failed to decode JSON response.";
				}
			}
		} else {
			$error = $err;
		}

		$this->setResult([$highestVersion, $artifactUrl, $api, $error]);
	}

	public function onCompletion() : void {
		[$highestVersion, $artifactUrl, $api, $err] = $this->getResult();

		if ($err !== null) {
			echo Terminal::$COLOR_RED . "[Sofia-Updater] Update notify error: $err" . Terminal::$COLOR_WHITE;
			return;
		}

		if ($highestVersion !== $this->pluginVersion) {
			echo Terminal::$COLOR_AQUA . "[Sofia-Updater] " . vsprintf("Version %s has been released for API %s. Download the new release at %s", ['v' . $highestVersion, $api, $artifactUrl]) . Terminal::$COLOR_WHITE;
		}
	}
}
