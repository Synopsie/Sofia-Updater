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
 * @version 1.1.0
 *
 */

declare(strict_types=1);

namespace sofia;

use Exception;
use pocketmine\Server;
use sofia\task\UpdaterAsyncTask;
use function debug_backtrace;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function in_array;
use const LOCK_EX;

final class Updater {
	public static function setToken(string $token, string $tokenFile) : void {
		try {
			file_put_contents($tokenFile, $token, LOCK_EX);
		} catch (Exception $e) {
			echo '§c[Error]' . $e->getMessage();
		}
	}

	public static function getToken(string $tokenFile) : ?string {
		try {
			if (!file_exists($tokenFile)) {
				return null;
			}
			// Check for debugging attempts
			if (self::isDebugging()) {
				throw new Exception('Access to token is blocked.');
			}
			return file_get_contents($tokenFile);
		} catch (Exception $e) {
			echo '§c[Error]' . $e->getMessage();
			return null;
		}
	}

	private static function isDebugging() : bool {
		$backtrace = debug_backtrace();
		foreach ($backtrace as $trace) {
			if (isset($trace['function']) && in_array($trace['function'], ['var_dump', 'print_r', 'debug_zval_dump'], true)) {
				return true;
			}
		}
		return false;
	}

	public static function checkUpdate(string $name, string $version, string $owner, string $repo, ?string $token = null) : void {
		Server::getInstance()->getAsyncPool()->submitTask(new UpdaterAsyncTask($name, $version, $owner, $repo, $token));
	}

}
