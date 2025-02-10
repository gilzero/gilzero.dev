## INTRODUCTION

Link content parser module extracts the bits that humans care about from any URL
you give it. That includes article content, titles, authors, published dates,
excerpts, lead images, and more.

It will be useful when you want to generate content from url.

## REQUIREMENTS
it has to download readability
**composer require fivefilters/readability.php:3.1.6**

- Install [Postlight Parser](https://github.com/postlight/parser) in your environment.
- Or install Mercury parser.
- Or prepare url postlight / mercury parser api.
- Otherwise, the module will get content by php readability
(this doesn't work well in some cases)
- Support Embera for media page like youtube, dailymotion, tiktok (Ckeditor5
must activate iframe, blockquote,...)
- If you want to use library Graby. You have to install php ext-tidy on your
server & run **composer require j0k3r/graby** manual
-
## CONFIGURATION
- Create link fields
- In widget select Postlight parser
- Map your field to get extracted content
- Add button Url get content in Ckeditor5 (/admin/config/content/formats)
Source editing active html tag iframe (Youtube), blockquote (tiktok),...
