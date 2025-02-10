How many times have you encountered a web app that breaks so utterly that all
you're presented with is a blank page? Often times that's because they render
their entire front-end in client-side JavaScript in an all-or-nothing fashion
instead of a layered, [progressively
enhanced](https://en.wikipedia.org/wiki/Progressive_enhancement) approach. We
all deserve a web that lasts for decades and prioritizes
[resiliency](https://resilientwebdesign.com/) over chasing whatever the current
shiny thing is. [The browser is the most hostile runtime
environment](https://molily.de/robust-javascript/#the-browser-as-a-runtime-environment)
after all:

> Writing client-side JavaScript for the web differs from programming for other platforms. There is not one well-defined runtime environment a developer may count on. There is not one hardware architecture or device type. There is not a single vendor that defines and builds the runtime, the compiler and the tools.

> [...]

> There are several relevant browsers in numerous versions running on different operating systems on devices with different hardware abilities, internet connectivity, etc. The fact that the web client is not under their control maddens developers from other domains. They see the web as the most hostile software runtime environment. They understand the diversity of web clients as a weakness.

> Proponents of the web counter that this heterogeneity and inconsistency is in fact a strength of the web. The web is open, it is everywhere, it has a low access threshold. The web is adaptive and keeps on absorbing new technologies and fields of applications.

An excellent argument for why server-rendered is the perfect fit for Drupal comes from [Ron Northcutt
(rlnorthcutt)](https://www.drupal.org/u/rlnorthcutt) on [[Plan] Gradually
replace Drupal's AJAX system with HTMX
[#3404409]](https://www.drupal.org/project/drupal/issues/3404409#comment-15579452) (edited for typos and grammar):

> ## Everything old is new again

> The reason that [HTMX](https://htmx.org/) is getting so much attention these days is similar to the increased focus we have seen on static sites. Speed, performance, and the recognition that things have gotten a bit out of hand for for building content focused websites. In this case, we see that the push for React/Angular/Vue as a full stack framework has led to ever more complex systems to build, tree shake, compile, partially render, and hydrate HTML code. This means that many, many applications are way over-engineered. So, there is a recognition that simpler approaches are just as good and often better. Highly reactive apps need a large JS framework, but most apps only need basic interactivity.

> The core argument *for* HTMX goes like this - you make a call to a server to find data, run business logic, and then format it as JSON which is returned to the client. Then the client has to do more work to render that JSON as HTML and put it on the page. So, why not just return the HTML from the server directly? It's faster, it's cacheable, and it reduces the load (both time and JS assets) in the browser.

> ## HTMX is a perfect fit for Drupal

> Drupal is incredibly good at letting us generate HTML, and the templating system means we can generate it down to the field level. So, with very minor changes, we should be able to allow Drupal core to easily return HTML fragments of any entity template. Add in display modes and views, and suddenly you have the ability to create an entire HTMX backend with little to no code. Thats insanely powerful, and can give the project a shot in the arm. Instead of building a custom backend in Python, Laravel, or Go...just use Drupal.

# Further reading

* [HTMX and the Rule of Least Power](https://blog.gypsydave5.com/posts/2024/4/12/htmx-and-the-rule-of-least-power/)
