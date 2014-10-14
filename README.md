Dropbox Markdown Server
===========

Markdown to HTML via Dropbox

A pretty simple app to transform text or markdown into a webpage. 
This app is two parts, a server side component where you want the pages to be hosted and the Dropbox folder where the original documents live.
The original documents are conveniently stored and accessible through Dropbox, so they can be edited anywhere, and it's all content no markup. 
The server listens for notifications from Dropbox, pulls the modified file, marks it up and "publishes" it.

## Setup
- Add the 'host' directory in your Dropbox
- Get a Dropbox access key and app name from [here](https://www.dropbox.com/developers/apps)
- Update the app.json with the required info
- Upload to server

### Notes
- Make sure the 'log' and 'cache' directories are writeable
- There's a 'fonts' directory under assets which you fill with your own fonts, then update the markdown.css

### The CSS:
The css in use is called 'dbx.css' and it's compiled from the three other sources and minified. You can of course bring your own page layout and replace the css file completely. The font reference is in markdown.css
