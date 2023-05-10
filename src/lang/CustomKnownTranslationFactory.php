<?php

declare(strict_types=1);

namespace jasonw4331\SimpleReplies\lang;

use pocketmine\lang\Translatable;

/**
 * This class contains factory methods for all the translations known to SimpleReplies.
 * This class is generated automatically, do NOT modify it by hand.
 *
 * @internal
 */
final class CustomKnownTranslationFactory{
	public static function command_reply_description() : Translatable{
		return new Translatable(CustomKnownTranslationKeys::COMMAND_REPLY_DESCRIPTION, []);
	}

	public static function command_reply_success(Translatable|string $param0, Translatable|string $param1) : Translatable{
		return new Translatable(CustomKnownTranslationKeys::COMMAND_REPLY_SUCCESS, [
			0 => $param0,
			1 => $param1,
		]);
	}

	public static function command_reply_usage() : Translatable{
		return new Translatable(CustomKnownTranslationKeys::COMMAND_REPLY_USAGE, []);
	}

}
