<?php
declare(strict_types=1);
namespace jasonwynn10\SimpleReplies;

use pocketmine\command\CommandSender;
use pocketmine\event\EventPriority;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\lang\Language;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\utils\AssumptionFailedError;
use Symfony\Component\Filesystem\Path;
use function array_merge;
use function mb_strtolower;
use function pathinfo;
use function scandir;
use function yaml_parse_file;

class Main extends PluginBase implements Listener{
	/** @var array<string, Language> $languages */
	private static array $languages = [];
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
				self::$languages[$languageName] = $language;
				foreach($languageAliases as $languageAlias => $alias){
					if(mb_strtolower($alias) === $languageName){
						self::$languages[mb_strtolower($languageAlias)] = $language;
						unset($languageAliases[$languageAlias]);
					}
				}
			}
		}

		// add translations to existing server language instance
		$languageA = $this->getServer()->getLanguage();
		$refClass = new \ReflectionClass($languageA);
		$refPropA = $refClass->getProperty('lang');
		$refPropA->setAccessible(true);
		/** @var string[] $langA */
		$langA = $refPropA->getValue($languageA);

		$languageB = self::$languages[$languageA->getLang()];
		$refClass = new \ReflectionClass($languageB);
		$refPropB = $refClass->getProperty('lang');
		$refPropB->setAccessible(true);
		/** @var string[] $langB */
		$langB = $refPropB->getValue($languageB);

		$refPropA->setValue($languageA, array_merge($langA, $langB));
	}

	/**
	 * @return array<string, Language>
	 */
	public static function getLanguages() : array{
		return self::$languages;
	}

	public function onMessage(CommandSender $sender, Player $receiver) : void {
		$this->lastSent[$receiver->getName()] = $sender->getName();
	}

	public function getWhoLastSent(string $recipient) : string {
		return $this->lastSent[$recipient] ?? "";
	}
}