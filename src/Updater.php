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
 * @version 1.0.0
 *
 */

declare(strict_types=1);

namespace sofia;

use pocketmine\Server;
use sofia\task\UpdaterAsyncTask;

final class Updater {
	public static function checkUpdate(string $name, string $version, string $owner, string $repo) : void {
		Server::getInstance()->getAsyncPool()->submitTask(new UpdaterAsyncTask($name, $version, $owner, $repo));
	}
}
