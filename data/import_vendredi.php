<?php
$link = mysql_connect('localhost', 'root', 'er45df12');
$db = mysql_select_db('mongobox', $link);
$req = mysql_query("SELECT * FROM videos_groups WHERE vendredi = 1");
while($row = mysql_fetch_assoc($req))
{
	$requete_une[] = "(1, ".mysql_escape_string($row['id_video']).")
		";
}
$requete_1 = "
INSERT INTO `video_tags` (`system_name` ,`name`) VALUES ('vendredi', 'vendredi');	
INSERT INTO `video_videos_tags` (`id_tag` ,`id_video`) VALUES ".implode(', ', $requete_une).";";

echo $requete_1;