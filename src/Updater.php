<?php
declare(strict_types=1);

namespace sofia;

use pocketmine\Server;
use sofia\task\UpdaterAsyncTask;

final class Updater {

    public static function checkUpdate(string $name, string $version, string $owner, string $repo): void {
        Server::getInstance()->getAsyncPool()->submitTask(new UpdaterAsyncTask($name, $version, $owner, $repo));
    }
}