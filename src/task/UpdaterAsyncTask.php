<?php
declare(strict_types=1);

namespace sofia\task;

use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\utils\Internet;

class UpdaterAsyncTask extends AsyncTask {

    private const GITHUB_RELEASES_URL = "https://api.github.com/repos/%s/%s/releases";

    public function __construct(
        private readonly string $pluginName,
        private readonly string $pluginVersion,
        private readonly string $githubOwner,
        private readonly string $githubRepo
    ) {}

    public function onRun(): void {
        $url = sprintf(self::GITHUB_RELEASES_URL, $this->githubOwner, $this->githubRepo);
        $json = Internet::getURL($url, 10, ['User-Agent: Sofia-Updater'], $err);
        $highestVersion = $this->pluginVersion;
        $artifactUrl = "";
        $api = "";
        if ($json !== null) {
            $releases = json_decode($json->getBody(), true);
            if ($releases !== null) {
                foreach ($releases as $release) {
                    $version = str_replace("v", "", $release["tag_name"]);
                    if (version_compare($highestVersion, $version, ">=")) {
                        continue;
                    }
                    $highestVersion = $version;
                    $artifactUrl = $release["html_url"];
                    $api = $this->pluginName;
                }
            }
        }

        $this->setResult([$highestVersion, $artifactUrl, $api, $err]);
    }

    public function onCompletion(): void {
        $plugin = Server::getInstance()->getPluginManager()->getPlugin($this->pluginName);
        if ($plugin === null) {
            return;
        }

        [$highestVersion, $artifactUrl, $api, $err] = $this->getResult();

        if ($err !== null) {
            $plugin->getLogger()->error("Update notify error: $err");
            return;
        }

        if ($highestVersion !== $this->pluginVersion) {
            $plugin->getLogger()->notice(vsprintf("Version %s has been released for API %s. Download the new release at %s", ['v' . $highestVersion, $api, $artifactUrl]));
        }
    }

}