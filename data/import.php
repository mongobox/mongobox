<?php
/*
 * Etape 1 : Executer import.sql
 * Etape 2 : Copier la tabe tumblr de l'ancienne vers la nouvelle base
 * Etape 3 : Editer import.php avec les bonnes valeurs de database et nom de table
 * Etape 4 : Executer import.php
 */
$association_users = array(
	19 => 1,
	2 => 2,
	14 => 3,
	15 => 4,
	31 => 5,
	20 => 6,
	12 => 7,
	7 => 8
);


$link = mysql_connect('localhost', 'root', '');
$db = mysql_select_db('jukebox', $link);
$req = mysql_query("SELECT * FROM videos");
while($row = mysql_fetch_assoc($req))
{
	$requete_une[] = "(".$row['id'].", '".mysql_escape_string($row['lien'])."', '".$row['date']."', '".mysql_escape_string($row['title'])."', ".$row['duration'].", '".mysql_escape_string($row['thumbnail'])."', '".mysql_escape_string($row['thumbnailHq'])."')
		";
	if(is_null($row['user_id'])) $user_id = 'NULL';
	else $user_id = $association_users[$row['user_id']];
	$requete_deux[] =  "(".$row['id'].", 1, ".$user_id.", '".$row['last_broadcast']."', ".$row['diffusion'].", ".$row['vendredi'].", ".$row['volume'].", ".$row['votes'].")
		";
}
$requete_1 = "INSERT INTO `videos` (`id` ,`lien` ,`date` ,`title` ,`duration` ,`thumbnail` ,`thumbnailHq`) VALUES ".implode(', ', $requete_une).";";
$requete_2 = "INSERT INTO `videos_groups` (
`id_video` ,
`id_group` ,
`user_id` ,
`last_broadcast` ,
`diffusion` ,
`vendredi` ,
`volume` ,
`votes`
)
VALUES ".implode(', ', $requete_deux).";";;

echo $requete_1;
echo $requete_2;

$requete = "";

$link = mysql_connect('localhost', 'root', '');
$db = mysql_select_db('mongobox', $link);
$req = mysql_query("SELECT * FROM tumblr");
while($row = mysql_fetch_assoc($req))
{
	$requete[] = "(".$row['id_tumblr'].", 2)";
}

$requete = "INSERT INTO `tumblrs_groups` (`id_tumblr`, `id_group`) VALUES ".implode(', ', $requete).";";
echo $requete;