<?php
declare(strict_types=1);
namespace jasonwynn10\SimpleReplies;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\defaults\VanillaCommand;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\lang\KnownTranslationFactory;
use pocketmine\permission\DefaultPermissionNames;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\plugin\PluginOwned;
use pocketmine\plugin\PluginOwnedTrait;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\TextFormat;

class Main extends PluginBase {
	/** @var string[] $lastSent */
	private array $lastSent;

	public function onEnable() : void {
		$commandMap = $this->getServer()->getCommandMap();
		$commandMap->unregister($commandMap->getCommand('tell') ?? throw new AssumptionFailedError('Tell command does not exist'));
		$commandMap->registerAll($this->getName(), [
			new commands\TellCommand('tell', $this),
			new commands\ReplyCommand('reply', $this)
		]);
	}

	public function onMessage(CommandSender $sender, Player $receiver) : void {
		$this->lastSent[$receiver->getName()] = $sender->getName();
	}

	public function getWhoLastSent(string $recipient) : string {
		return $this->lastSent[$recipient] ?? "";
	}
}