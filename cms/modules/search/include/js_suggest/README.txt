Suggest Framework
Copyright (c) 2005 Matthew Ratzloff

Version 0.2

Overview
--------
Suggest Framework allows developers to easily add "suggest" functionality 
to their websites and projects, which can vastly improve the user experience 
by speeding up phrase-based searching.  Any number of search boxes can be 
used on a page, each configurable with a variety of options.

Suggest Framework is also compatible with nearly all mainstream browsers, 
including Internet Explorer 5+ (Win/Mac), Firefox (Win/Mac), and Opera 8+.  
It... sort of works with Safari.

Installation
------------
You only need one copy of SuggestFramework.js on your server in order to 
use it throughout.  You can customize the look of the suggest dropdowns 
with CSS; these styles should be included in your sitewide stylesheet and 
adjusted per-page if necessary.

Note: The JavaScript file has been compressed for speed using Dean Edwards's 
Packer utility, which can be found at <http://dean.edwards.name/packer/>.

Usage
-----
Include the following two lines in the head of the page:

<script type="text/javascript" src="/path/to/SuggestFramework.js"></script>
<script type="text/javascript">window.onload = initializeSuggestFramework;</script>

Now you have five additional attributes available for any named textbox:

action    The dynamic page that accepts input by GET and returns a 
          JavaScript array of relevant data.  Required.

capture   The column (from 1) that will replace the user input.  Generally 
          this should be the same database field that the user is searching 
          against.  Optional; default is 1.

columns   The number of columns to display in the dropdown.  For example, 
          you might search for employees by name and display their ID 
          on the right.  Optional; default is 1.

delay     The search delay, in milliseconds.  A lower delay increases 
          responsiveness but puts more strain on the server.  Optional; 
          default is 1000 (1 second).

heading   If set to true, uses first array value as a non-selectable 
          header.  Useful when you have two or more columns.  Optional; 
          default is false.

The page that processes the user input (defined in "action") accepts two 
parameters:

type      The name of the textbox
q         The query phrase

Suggested examples for PHP and ColdFusion have been included, although 
any server-side language will work.  For more than one column, a multi-
dimensional array is expected.  For example,

new Array(new Array("A1", "B1"), new Array("A2", "B2"));

Finally, there are four CSS classes:

.SuggestFramework_List         The dropdown container
.SuggestFramework_Heading      The optional dropdown headings
.SuggestFramework_Highlighted  The highlighted suggestion
.SuggestFramework_Normal       Non-highlighted suggestions

Release History
---------------

0.2 - Initial beta release.  Revised to be procedural instead of object-oriented 
      in order to increase compatibility.  Compatible with Internet 
      Explorer 5+ (Win/Mac), Firefox (Win/Mac), and Opera 8+.  Partial 
      compatibility with Safari.

0.1 - Unreleased alpha version.  Compatible with Internet Explorer 5.5+ (Win), 
      Firefox (Win/Mac), and Opera 8+.  Not compatible with Safari.