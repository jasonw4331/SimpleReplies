<?php
declare(strict_types=1);
namespace jasonwynn10\SimpleReplies;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\defaults\VanillaCommand;
use pocketmine\command\utils\CommandException;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\lang\TranslationContainer;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;

class Main extends PluginBase {
	/** @var string[] $lastSent */
	private $lastSent;

	public function onEnable() {
		/** @var VanillaCommand $tellCommand */
		$tellCommand = $this->getServer()->getCommandMap()->getCommand("tell");
		$this->getServer()->getCommandMap()->unregister($tellCommand);
		$this->getServer()->getCommandMap()->register("pocketmine", new class("tell", $this) extends VanillaCommand {
			private $plugin;
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

			public function execute(CommandSender $sender, string $commandLabel, array $args) {
				if(!$this->testPermission($sender)){
					return true;
				}

				if(count($args) < 2){
					throw new InvalidCommandSyntaxException();
				}

				$player = $sender->getServer()->getPlayer(array_shift($args));

				if($player === $sender){
					$sender->sendMessage(new TranslationContainer(TextFormat::RED . "%commands.message.sameTarget"));
					return true;
				}

				if($player instanceof Player){
					$sender->sendMessage("[{$sender->getName()} -> {$player->getDisplayName()}] " . implode(" ", $args));
					$name = $sender instanceof Player ? $sender->getDisplayName() : $sender->getName();
					$player->sendMessage("[$name -> {$player->getName()}] " . implode(" ", $args));
					$this->plugin->onMessage($sender, $player);
				}else{
					$sender->sendMessage(new TranslationContainer("commands.generic.player.notFound"));
				}

				return true;
			}
		});
		$this->getServer()->getCommandMap()->register("SimpleReplies", new class("reply", $this) extends Command {
			private $plugin;
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
			 * @return mixed
			 * @throws CommandException
			 */
			public function execute(CommandSender $sender, string $commandLabel, array $args) {
				if(!$this->testPermission($sender)) {
					return true;
				}

				if(count($args) < 1) {
					throw new InvalidCommandSyntaxException();
				}

				/** @var CommandSender $player */
				if(!empty($this->plugin->getLastSent($sender->getName())) or !($player = $this->plugin->getServer()->getPlayer($this->plugin->getLastSent($sender->getName()))) instanceof CommandSender) {
					$sender->sendMessage("[{$sender->getName()} -> {$player->getDisplayName()}] " . implode(" ", $args));
					$name = $sender instanceof Player ? $sender->getDisplayName() : $sender->getName();
					$player->sendMessage("[$name -> {$player->getName()}] " . implode(" ", $args));
					$this->plugin->onMessage($sender, $player);
				}else{
					$sender->sendMessage(new TranslationContainer("commands.generic.player.notFound"));
				}

				return true;
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