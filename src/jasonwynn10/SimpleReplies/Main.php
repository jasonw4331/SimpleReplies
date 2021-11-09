<?php
declare(strict_types=1);
namespace jasonwynn10\SimpleReplies;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\defaults\VanillaCommand;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\command\utils\CommandException;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\lang\TranslationContainer;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\TextFormat;

class Main extends PluginBase {
	/** @var string[] $lastSent */
	private array $lastSent;

	public function onEnable() {
		$commandMap = $this->getServer()->getCommandMap();
		$commandMap->unregister($commandMap->getCommand('tell') ?? throw new AssumptionFailedError('Tell command does not exist'));
		$commandMap->register("pocketmine", new class("tell", $this) extends VanillaCommand {
			private Main $plugin;
			public function __construct(string $name, Main $plugin) {
				$this->plugin = $plugin;
				parent::__construct(
					$name,
					"%pocketmine.command.tell.description",
					"%commands.message.usage",
					["w", "msg"]
				);
				$this->setPermission("pocketmine.command.tell");
			}

			public function execute(CommandSender $sender, string $commandLabel, array $args) : void {
				if(!$this->testPermission($sender)){
					return;
				}

				if(count($args) < 2){
					throw new InvalidCommandSyntaxException();
				}

				$player = $sender->getServer()->getPlayer(array_shift($args));

				if($player === $sender){
					$sender->sendMessage(new TranslationContainer(TextFormat::RED . "%commands.message.sameTarget"));
					return;
				}

				if($player instanceof Player){
					$sender->sendMessage("[{$sender->getName()} -> {$player->getDisplayName()}] " . implode(" ", $args));
					$name = $sender instanceof Player ? $sender->getDisplayName() : $sender->getName();
					$player->sendMessage("[$name -> {$player->getName()}] " . implode(" ", $args));
					$this->plugin->onMessage($sender, $player);
				}else{
					$sender->sendMessage(new TranslationContainer("commands.generic.player.notFound"));
				}
			}
		});
		$commandMap->register("SimpleReplies", new class("reply", $this) extends Command implements PluginIdentifiableCommand {
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

			/**
			 * @param CommandSender $sender
			 * @param string $commandLabel
			 * @param string[] $args
			 *
			 * @throws CommandException
			 */
			public function execute(CommandSender $sender, string $commandLabel, array $args) : void {
				if(!$this->testPermission($sender)) {
					return;
				}

				if(count($args) < 1) {
					throw new InvalidCommandSyntaxException();
				}

				if($this->plugin->getLastSent($sender->getName()) !== "") {
					$player = $this->plugin->getServer()->getPlayer($this->plugin->getLastSent($sender->getName()));
						if($player instanceof CommandSender) {
							$sender->sendMessage("[{$sender->getName()} -> {$player->getDisplayName()}] " . implode(" ", $args));
							$name = $sender instanceof Player ? $sender->getDisplayName() : $sender->getName();
							$player->sendMessage("[$name -> {$player->getName()}] " . implode(" ", $args));
							$this->plugin->onMessage($sender, $player);
						}else{
							$sender->sendMessage(new TranslationContainer("commands.generic.player.notFound"));
						}
				}else{
					$sender->sendMessage(new TranslationContainer("commands.generic.player.notFound"));
				}
			}

			public function getPlugin() : Plugin {
				return $this->plugin;
			}
		});
	}

	/**
	 * @param CommandSender $sender
	 * @param Player $receiver
	 */
	public function onMessage(CommandSender $sender, Player $receiver) : void {
		$this->lastSent[$receiver->getName()] = $sender->getName();
	}

	/**
	 * @param string $name
	 *
	 * @return string
	 */
	public function getLastSent(string $name) : string {
		return $this->lastSent[$name] ?? "";
	}
}