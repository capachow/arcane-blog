## Arcane Blog

A simple flat-file blog that uses Markdown. Perfect for personal sites or those getting started with [Arcane](http://arcane.dev).

> #### [Preview Blog Site](https://blog.arcane.dev)

## Basics

- Create `slug.md` files inside `/pages/posts/` to make new posts.
- Use optional `YYYY-MM-DD-slug.md` format for forced create dates.

> It's that simple.

## Support

  - Requires PHP >= 8.2.
  - **Apache users:** Works automatically provided the `AllowOverride All` directive is enabled (Arcane will generate the required `.htaccess` file for you).
  - **NGINX users:** Requires routing traffic to the front controller. Add this to your `server` block alongside your existing PHP execution directive:
```nginx
location / {
  rewrite ^/(.*)/$ /$1 permanent;
  try_files $uri $uri/ /index.php?$query_string;
}
```
  - [Creating an issue](https://github.com/capachow/arcane-blog/issues) on GitHub for reporting bugs is always appreciated.

## License

Copyright 2017-2026 [Joshua Britt](https://github.com/capachow) under the [MIT](LICENSE.md).