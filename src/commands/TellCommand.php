<?php

declare(strict_types=1);

namespace jasonwynn10\SimpleReplies\commands;

use jasonwynn10\SimpleReplies\Main;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\defaults\VanillaCommand;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\lang\KnownTranslationFactory;
use pocketmine\permission\DefaultPermissionNames;
use pocketmine\player\Player;
use pocketmine\plugin\PluginOwned;
use pocketmine\plugin\PluginOwnedTrait;
use pocketmine\utils\TextFormat;
use function array_shift;
use function count;
use function implode;

class TellCommand extends VanillaCommand implements PluginOwned{
	use PluginOwnedTrait {
		__construct as private setOwningPlugin;
	}

	public function __construct(string $name, Main $owningPlugin){
		parent::__construct(
			$name,
			KnownTranslationFactory::pocketmine_command_tell_description(),
			KnownTranslationFactory::commands_message_usage(),
			["w", "msg"]
		);
		$this->setOwningPlugin($owningPlugin);
		$this->setPermission(DefaultPermissionNames::COMMAND_TELL);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args) : void{
		if(!$this->testPermission($sender)){
			return;
		}

		if(count($args) < 2){
			throw new InvalidCommandSyntaxException();
		}

		$search = array_shift($args);

		$found = str_contains(mb_strtoupper($search), Main::getConsoleCommandSender()->getName()) ?
			Main::getConsoleCommandSender() :
			$sender->getServer()->getPlayerByPrefix($search);

		if($found === $sender){
			$sender->sendMessage(KnownTranslationFactory::commands_message_sameTarget()->prefix(TextFormat::RED));
			return;
		}

		if($found instanceof CommandSender){
			$message = implode(" ", $args);
			$sender->sendMessage(KnownTranslationFactory::commands_message_display_outgoing($found->getDisplayName(), $message)->prefix(TextFormat::GRAY . TextFormat::ITALIC));
			$name = $sender instanceof Player ? $sender->getDisplayName() : $sender->getName();
			$found->sendMessage(KnownTranslationFactory::commands_message_display_incoming($name, $message)->prefix(TextFormat::GRAY . TextFormat::ITALIC));
			Command::broadcastCommandMessage($sender, KnownTranslationFactory::commands_message_display_outgoing($found->getDisplayName(), $message), false);
			$this->owningPlugin->onMessage($sender, $found);
		}else{
			$sender->sendMessage(KnownTranslationFactory::commands_generic_player_notFound());
		}
	}
}
