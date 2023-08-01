<?php

declare(strict_types=1);

namespace jasonw4331\SimpleReplies\commands;

use jasonw4331\SimpleReplies\lang\CustomKnownTranslationFactory;
use jasonw4331\SimpleReplies\Main;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\lang\KnownTranslationFactory;
use pocketmine\player\Player;
use pocketmine\plugin\PluginOwned;
use pocketmine\plugin\PluginOwnedTrait;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use function count;
use function implode;
use function mb_strtoupper;
use function str_contains;

class ReplyCommand extends Command implements PluginOwned{
	use PluginOwnedTrait {
		__construct as private setOwningPlugin;
	}

	public function __construct(string $name, Main $owningPlugin){
		parent::__construct(
			$name,
			CustomKnownTranslationFactory::command_reply_description(),
			CustomKnownTranslationFactory::command_reply_usage(),
			["r"]
		);
		$this->setOwningPlugin($owningPlugin);
		$this->setPermission("SimpleReplies.reply");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args) : void{
		if(!$this->testPermission($sender)){
			return;
		}

		if(count($args) < 1){
			throw new InvalidCommandSyntaxException();
		}

		$lastSent = $this->owningPlugin->getWhoLastSent($sender);
		if($lastSent !== ""){
			$found = str_contains(mb_strtoupper($lastSent), "CONSOLE") ?
				Main::getConsoleCommandSender() :
				Server::getInstance()->getPlayerExact($lastSent);
			if($found instanceof CommandSender){
				$foundName = $found instanceof Player ? $found->getDisplayName() : $found->getName();
				$sender->sendMessage(CustomKnownTranslationFactory::command_reply_success($sender->getName(), $foundName . TextFormat::RESET)->postfix(implode(" ", $args)));
				$senderName = $sender instanceof Player ? $sender->getDisplayName() : $sender->getName();
				$found->sendMessage(CustomKnownTranslationFactory::command_reply_success($senderName . TextFormat::RESET, $found->getName())->postfix(implode(" ", $args)));
				$this->owningPlugin->onMessage($sender, $found);
				return;
			}
		}
		$sender->sendMessage(KnownTranslationFactory::commands_generic_player_notFound());
	}
}
