<?php

declare(strict_types=1);

namespace jasonw4331\SimpleReplies;

use pocketmine\command\CommandSender;
use pocketmine\console\ConsoleCommandSender;
use pocketmine\event\EventPriority;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\lang\Language;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\Utils;
use ReflectionClass;
use Symfony\Component\Filesystem\Path;
use function array_merge;
use function mb_strtolower;
use function pathinfo;
use function scandir;
use function yaml_parse_file;

class Main extends PluginBase implements Listener{
	private static ConsoleCommandSender $consoleCommandSender;
	/** @var string[] $lastSent */
	private array $lastSent;

	public function onEnable() : void{
		// register commands
		$commandMap = $this->getServer()->getCommandMap();
		$commandMap->unregister($commandMap->getCommand('tell') ?? throw new AssumptionFailedError('Tell command does not exist'));
		$commandMap->registerAll($this->getName(), [
			new commands\TellCommand('tell', $this),
			new commands\ReplyCommand('reply', $this)
		]);

		// register events
		$this->getServer()->getPluginManager()->registerEvent(
			PlayerQuitEvent::class,
			function(PlayerQuitEvent $event) : void{ unset($this->lastSent[$event->getPlayer()->getName()]); },
			EventPriority::NORMAL,
			$this
		);

		$this->saveResource('/lang/config.yml');
		/** @var string[][] $contents */
		$contents = yaml_parse_file(Path::join($this->getDataFolder(), "lang", 'config.yml'));
		$languageAliases = [];
		foreach($contents as $language => $aliases){
			$mini = mb_strtolower($aliases['mini']);
			$this->saveResource('/lang/data/' . $mini . '.ini');
			$languageAliases[$mini] = $language;
		}

		$languages = [];
		$dir = scandir(Path::join($this->getDataFolder(), "lang", "data"));
		if($dir !== false){
			foreach($dir as $file){
				/** @phpstan-var array{dirname: string, basename: string, extension?: string, filename: string} $fileData */
				$fileData = pathinfo($file);
				if(!isset($fileData["extension"]) || $fileData["extension"] !== "ini"){
					continue;
				}
				$languageName = mb_strtolower($fileData["filename"]);
				$language = new Language(
					$languageName,
					Path::join($this->getDataFolder(), "lang", "data")
				);
				$languages[$languageName] = $language;
				foreach(Utils::stringifyKeys($languageAliases) as $languageAlias => $alias){
					if(mb_strtolower($alias) === $languageName){
						$languages[mb_strtolower($languageAlias)] = $language;
						unset($languageAliases[$languageAlias]);
					}
				}
			}
		}

		// add translations to existing server language instance
		$languageA = $this->getServer()->getLanguage();
		$refClass = new ReflectionClass($languageA::class);
		$refPropA = $refClass->getProperty('lang');
		/** @var string[] $langA */
		$langA = $refPropA->getValue($languageA);
		/** @var string[] $langB */
		$langB = $refClass->getProperty('lang')->getValue($languages[$languageA->getLang()]);
		$refPropA->setValue($languageA, array_merge($langA, $langB));
	}

	public function onMessage(CommandSender $sender, CommandSender $receiver) : void{
		$this->lastSent[$receiver->getName()] = $sender->getName();
	}

	public function getWhoLastSent(CommandSender $recipient) : string{
		return $this->lastSent[$recipient->getName()] ?? "";
	}

	public static function getConsoleCommandSender() : ConsoleCommandSender{
		self::$consoleCommandSender ??= (new ReflectionClass(Server::getInstance()))->getProperty('consoleSender')->getValue(Server::getInstance());
		return self::$consoleCommandSender;
	}
}
