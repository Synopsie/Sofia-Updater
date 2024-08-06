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

namespace sofia;

use Exception;
use pocketmine\Server;
use sofia\task\UpdaterAsyncTask;
use function base64_encode;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function openssl_decrypt;
use function openssl_encrypt;
use function random_bytes;
use const LOCK_EX;

final class Updater {
	/**
	 * @experimental
	 */
	private static string $encryptionKey;

	/**
	 * @experimental
	 */
	private const ENCRYPTION_METHOD = 'AES-128-ECB';

	/**
	 * @experimental
	 */
	private static function encrypt(string $data, ?string $key = null) : string {
		return openssl_encrypt($data, self::ENCRYPTION_METHOD, $key ?? self::$encryptionKey);
	}

	/**
	 * @experimental
	 */
	private static function decrypt(string $data, ?string $key = null) : string {
		return openssl_decrypt($data, self::ENCRYPTION_METHOD, $key ?? self::$encryptionKey);
	}

	/**
	 * @experimental
	 */
	public static function setToken(string $token, string $file) : void {
		try {
			$encryptedToken = self::encrypt($token, self::$encryptionKey);
			file_put_contents($file, $encryptedToken, LOCK_EX);
		} catch (Exception $e) {
			Server::getInstance()->getLogger()->error('Error while setting token: ' . $e->getMessage());
		}
	}

	/**
	 * @experimental
	 */
	public static function getToken(string $file) : ?string {
		try {
			if (!file_exists($file)) {
				return null;
			}
			$encryptedToken = file_get_contents($file);
			return self::decrypt($encryptedToken, self::$encryptionKey);
		} catch (Exception $e) {
			Server::getInstance()->getLogger()->error('Error while getting token: ' . $e->getMessage());
			return null;
		}
	}

	public static function checkUpdate(string $name, string $version, string $owner, string $repo, ?string $token = null) : void {
		Server::getInstance()->getAsyncPool()->submitTask(new UpdaterAsyncTask($name, $version, $owner, $repo, $token));
	}

	/**
	 * @throws Exception
	 * @experimental
	 */
	public static function generateEncryptionKey(int $length = 32) : string {
		return base64_encode(random_bytes($length));
	}
}
