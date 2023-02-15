<?php

declare(strict_types=1);

namespace jasonwynn10\SimpleReplies\commands;

use jasonwynn10\SimpleReplies\Main;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\lang\KnownTranslationFactory;
use pocketmine\player\Player;
use pocketmine\plugin\PluginOwned;
use pocketmine\plugin\PluginOwnedTrait;
use function count;
use function implode;

class ReplyCommand extends Command implements PluginOwned{
	use PluginOwnedTrait {
		__construct as private setOwningPlugin;
	}

	public function __construct(string $name, Main $owningPlugin){
		parent::__construct(
			$name,
			"Reply to the last received message",
			"/r <message: string>",
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

		if($this->owningPlugin->getWhoLastSent($sender->getName()) !== ""){
			$player = $this->owningPlugin->getServer()->getPlayerExact($this->owningPlugin->getWhoLastSent($sender->getName()));
			if($player instanceof CommandSender){
				$sender->sendMessage("[{$sender->getName()} -> {$player->getDisplayName()}] " . implode(" ", $args));
				$name = $sender instanceof Player ? $sender->getDisplayName() : $sender->getName();
				$player->sendMessage("[$name -> {$player->getName()}] " . implode(" ", $args));
				$this->owningPlugin->onMessage($sender, $player);
				return;
			}
		}
		$sender->sendMessage(KnownTranslationFactory::commands_generic_player_notFound());
	}
}
