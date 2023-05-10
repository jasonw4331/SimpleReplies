<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
	'message' => '#^Static property jasonw4331\\\\SimpleReplies\\\\Main\\:\\:\\$consoleCommandSender \\(pocketmine\\\\console\\\\ConsoleCommandSender\\|null\\) does not accept mixed\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Main.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method pocketmine\\\\plugin\\\\Plugin\\:\\:getWhoLastSent\\(\\)\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/commands/ReplyCommand.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method pocketmine\\\\plugin\\\\Plugin\\:\\:onMessage\\(\\)\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/commands/ReplyCommand.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot call method getName\\(\\) on pocketmine\\\\console\\\\ConsoleCommandSender\\|null\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/commands/ReplyCommand.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method pocketmine\\\\console\\\\ConsoleCommandSender\\|pocketmine\\\\player\\\\Player\\:\\:getDisplayName\\(\\)\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/src/commands/TellCommand.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method pocketmine\\\\plugin\\\\Plugin\\:\\:onMessage\\(\\)\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/commands/TellCommand.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot call method getName\\(\\) on pocketmine\\\\console\\\\ConsoleCommandSender\\|null\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/commands/TellCommand.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
