# About Jaculus web frontend

Made for artisans by giving introspection abilities to templates. More clearly this allows less backend programming.
By allowing PHP modules to connect to templates directly and by using the primitives user permissions and routes more specific
modules can written and reused everywhere with very little PHP. An example would be a facebook module collection.
There it could be modules Facebook.login and Facebook.user which values can be presented on a template with only a few
PHP lines.

# Working with others

I am huge fan open source and always want to take advantage of that. Therefore everything this frontend does is taking
the Twig template engine and creates a system where injecting variables into those templates.
With this you can use whatever fullblown framework you want and it will just work.

# License

All Jaculus related work is licensed under the LGPL version 3 license. 