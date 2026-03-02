<?php

$stamp = filemtime(path(['PAGES', 'posts'], true));
$json = path(['HELPERS', 'index/posts.json'], true);

if(!file_exists($json)) {
  file_put_contents($json, '{}');
}

$cache = json_decode(file_get_contents($json), true);

if(!isset($cache[$stamp])) {
  $cache = reset($cache);

  foreach(glob(path(['PAGES', 'posts/*.md'], true)) as $path) {
    $md = $slug = strtolower(basename($path, '.md'));
    $modified = filemtime($path);

    if(strpos($md, '-', 8) == 10) {
      $regex = "/^(\d{4}-\d{2}-\d{2})-(.+)$/";

      if(preg_match($regex, $slug, $basename)) {
        $slug = $basename[2];
      }
    }

    if(!isset($cache[$md])) {
      if(strpos($md, '-', 8) == 10) {
        $modified = strtotime($basename[1]);
      }

      $posts[$md] = [
        'path' => $path,
        'slug' => $slug,
        'created' => $modified,
        'modified' => $modified
      ];
    } else {
      $posts[$md] = $cache[$md];

      if($modified != $cache[$md]['modified']) {
        $posts[$md]['modified'] = $modified;
      }
    }
  }

  uasort($posts, function($a, $b) {
    return $b['created'] - $a['created'];
  });

  file_put_contents($json, json_encode([$stamp => $posts]));
} else {
  $posts = $cache[$stamp];
}

return array_filter(array_merge([0], $posts));

?>