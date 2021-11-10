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
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginBase;
use pocketmine\plugin\PluginOwned;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\TextFormat;

class Main extends PluginBase {
	/** @var string[] $lastSent */
	private array $lastSent;

	public function onEnable() : void {
		$commandMap = $this->getServer()->getCommandMap();
		$commandMap->unregister($commandMap->getCommand('tell') ?? throw new AssumptionFailedError('Tell command does not exist'));
		$commandMap->register("pocketmine", new class("tell") extends VanillaCommand {
			private Main $plugin;
			public function __construct(string $name, Main $plugin){
				$this->plugin = $plugin;
				parent::__construct(
					$name,
					KnownTranslationFactory::pocketmine_command_tell_description(),
					KnownTranslationFactory::commands_message_usage(),
					["w", "msg"]
				);
				$this->setPermission(DefaultPermissionNames::COMMAND_TELL);
			}

			public function execute(CommandSender $sender, string $commandLabel, array $args) : void {
				if(!$this->testPermission($sender)){
					return;
				}

				if(count($args) < 2){
					throw new InvalidCommandSyntaxException();
				}

				$player = $sender->getServer()->getPlayerByPrefix(array_shift($args));

				if($player === $sender){
					$sender->sendMessage(KnownTranslationFactory::commands_message_sameTarget()->prefix(TextFormat::RED));
					return;
				}

				if($player instanceof Player){
					$message = implode(" ", $args);
					$sender->sendMessage(KnownTranslationFactory::commands_message_display_outgoing($player->getDisplayName(), $message)->prefix(TextFormat::GRAY . TextFormat::ITALIC));
					$name = $sender instanceof Player ? $sender->getDisplayName() : $sender->getName();
					$player->sendMessage(KnownTranslationFactory::commands_message_display_incoming($name, $message)->prefix(TextFormat::GRAY . TextFormat::ITALIC));
					Command::broadcastCommandMessage($sender, KnownTranslationFactory::commands_message_display_outgoing($player->getDisplayName(), $message), false);
					$this->plugin->onMessage($sender, $player);
				}else{
					$sender->sendMessage(KnownTranslationFactory::commands_generic_player_notFound());
				}
			}
		});
		$commandMap->register("SimpleReplies", new class("reply", $this) extends Command implements PluginOwned {
			private Main $plugin;
			public function __construct(string $name, Main $plugin) {
				$this->plugin = $plugin;
				parent::__construct(
					$name,
					"Reply to the last received message",
					"/r <message: string>",
					["r"]
				);
				$this->setPermission("SimpleReplies.reply");
			}

			public function execute(CommandSender $sender, string $commandLabel, array $args) : void {
				if(!$this->testPermission($sender)) {
					return;
				}

				if(count($args) < 1) {
					throw new InvalidCommandSyntaxException();
				}

				if($this->plugin->getWhoLastSent($sender->getName()) !== "") {
					$player = $this->plugin->getServer()->getPlayerExact($this->plugin->getWhoLastSent($sender->getName()));
					if($player instanceof CommandSender) {
						$sender->sendMessage("[{$sender->getName()} -> {$player->getDisplayName()}] " . implode(" ", $args));
						$name = $sender instanceof Player ? $sender->getDisplayName() : $sender->getName();
						$player->sendMessage("[$name -> {$player->getName()}] " . implode(" ", $args));
						$this->plugin->onMessage($sender, $player);
						return;
					}
				}
				$sender->sendMessage(KnownTranslationFactory::commands_generic_player_notFound());
			}

			public function getOwningPlugin() : Plugin {
				return $this->plugin;
			}
		});
	}

	public function onMessage(CommandSender $sender, Player $receiver) : void {
		$this->lastSent[$receiver->getName()] = $sender->getName();
	}

	public function getWhoLastSent(string $recipient) : string {
		return $this->lastSent[$recipient] ?? "";
	}
}