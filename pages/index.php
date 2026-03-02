<?php

/**
 * Arcane Blog 26.03.1 MIT
**/

if(path(1) == 'post') {
  $posts = array_column($posts, null, 'slug');
  $post = $content($posts[path(2)]);

  define('ROUTES', [
    ['post', array_keys($posts)]
  ]);

  if(array_key_exists('heading', $post)) {
    relay('TITLE', trim(ltrim($post['heading'], '#')));
  }
} else {
  $page = path(2) ?? 1;
  $pages = $paginate($posts, 5);
  $posts = array_map($content, $pages[$page]);

  define('ROUTES', [
    ['page', range(2, count($pages))]
  ]);
}

?>

<?php if(path(1) == 'post') { ?>
  <article>
    <time>
      <span><?= date('M j, Y', $post['created']); ?></span>
      <span><?= $readtime($post['content']); ?></span>
    </time>
    <?= $markdown($post['content']); ?>
    <?php if($post['modified'] > $post['created']) { ?>
      <time><i><?= scribe('Last updated on :date', [
        ':date' => date('M j, Y', $post['modified'])
      ]) ?></i></time>
    <?php } ?>
  </article>
<?php } else { ?>
  <?php foreach($posts as $post) { ?>
    <article>
      <time>
        <span><?= date('M j, Y', $post['created']); ?></span>
        <span><?= $readtime($post['content']); ?></span>
      </time>
      <?php $link = path("/post/{$post['slug']}/"); ?>
      <?php if(array_key_exists('heading', $post)) { ?>
        <?php $regex = "/^[\s]*(\#+)\s+(.+)$/"; ?>
        <?= $markdown([
          preg_replace($regex, "$1 [$2]({$link})", $post['heading'])
        ]); ?>
      <?php } ?>
      <p><?= implode('&nbsp;', [
        $truncate(strip_tags($markdown($post['preview'])), 180),
        $anchor('continue', $link)
      ]); ?></p>
    </article>
  <?php } ?>
  <?php if(count($pages) > 1) { ?>
    <aside>
      <?php if($pages[$prev = $page - 1] ?? false) { ?>
        <?php $link = $prev == 1 ? '/' : "/page/{$prev}/"; ?>
        <?= $anchor('&larr;', path($link)); ?>
      <?php } else { ?>
        <span>&larr;</span>
      <?php } ?>
      <nav>
        <?php foreach(array_keys($pages) as $key) { ?>
          <?php if($key == $page) { ?>
            <span><?= $key; ?></span>
          <?php } else { ?>
            <?php $link = $key == 1 ? '/' : "/page/{$key}/"; ?>
            <?= $anchor($key, path($link)); ?>
          <?php } ?>
        <?php } ?>
      </nav>
      <?php if($pages[$next = $page + 1] ?? false) { ?>
        <?= $anchor('&rarr;', path("/page/{$next}/")); ?>
      <?php } else { ?>
        <span>&rarr;</span>
      <?php } ?>
    </aside>
  <?php } ?>
<?php } ?>