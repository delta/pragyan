/*
        paginate table object v1.6 by frequency-decoder.com

        Released under a creative commons Attribution-ShareAlike 2.5 license (http://creativecommons.org/licenses/by-sa/2.5/)

        Please credit frequency decoder in any derivative work - thanks

        You are free:

        * to copy, distribute, display, and perform the work
        * to make derivative works
        * to make commercial use of the work

        Under the following conditions:

                by Attribution.
                --------------
                You must attribute the work in the manner specified by the author or licensor.

                sa
                --
                Share Alike. If you alter, transform, or build upon this work, you may distribute the resulting work only under a license identical to this one.

        * For any reuse or distribution, you must make clear to others the license terms of this work.
        * Any of these conditions can be waived if you get permission from the copyright holder.
*/
tablePaginater = {
        tableInfo: {},
        uniqueID:0,
        /*

        Localise the button titles here...

        %p is replaced with the appropriate page number
        %t is replaced with the total number of pages
        
        */
        text: ["First Page","Previous Page (Page %p)","Next Page (Page %p)","Last Page (Page %t)","Page %p of %t"],
        
        addEvent: function(obj, type, fn, tmp) {
                tmp || (tmp = true);
                if( obj.attachEvent ) {
                        obj["e"+type+fn] = fn;
                        obj[type+fn] = function(){obj["e"+type+fn]( window.event );};
                        obj.attachEvent( "on"+type, obj[type+fn] );
                } else {
                        obj.addEventListener( type, fn, true );
                };
        },

        addClass: function(e,c) {
                if(new RegExp("(^|\\s)" + c + "(\\s|$)").test(e.className)) return;
                e.className += ( e.className ? " " : "" ) + c;
        },

        /*@cc_on
        /*@if (@_win32)
        removeClass: function(e,c) {
                e.className = !c ? "" : e.className.replace(new RegExp("(^|\\s)" + c + "(\\s|$)"), " ").replace(/^\s*((?:[\S\s]*\S)?)\s*$/, '$1');
        },
        @else @*/
        removeClass: function(e,c) {
                e.className = !c ? "" : e.className.replace(new RegExp("(^|\\s)" + c + "(\\s|$)"), " ").replace(/^\s\s*/, '').replace(/\s\s*$/, '');
        },
        /*@end
        @*/
        
        init: function(tableId) {
                var tables = tableId && typeof(tableId) == "string" ? [document.getElementById(tableId)] : document.getElementsByTagName('table');
                var hook, maxPages, visibleRows, numPages, cp, cb, rowList;
                
                for(var t = 0, tbl; tbl = tables[t]; t++) {
                        if(tbl.className.search(/paginate-([0-9]+)/) == -1) { continue; };

                        if(!tbl.id) { tbl.id = "fdUniqueTableId_" + tablePaginater.uniqueID++; };

                        maxPages = tbl.className.search(/max-pages-([0-9]+)/) == -1 ? null : Number(tbl.className.match(/max-pages-([0-9]+)/)[1]);
                        if(maxPages % 2 == 0 && maxPages > 1) { maxPages--; };
                        
                        hook = tbl.getElementsByTagName('tbody');
                        hook = (hook.length) ? hook[0] : tbl;

                        visibleRows = tablePaginater.calculateVisibleRows(hook);
                        
                        if(maxPages > (visibleRows / Number(tbl.className.match(/paginate-([0-9]+)/)[1]))) {
                                maxPages = null;
                        };
                        
                        numPages = Math.ceil(visibleRows / Number(tbl.className.match(/paginate-([0-9]+)/)[1]));
                        
                        if(numPages < 2 && !(tbl.id in tablePaginater.tableInfo)) {
                                continue;
                        };
                        
                        cp = (tbl.id in tablePaginater.tableInfo) ? Math.min(tablePaginater.tableInfo[tbl.id].currentPage, numPages) : 1;

                        cb = tbl.className.search(/paginationcallback-([\S-]+)/) == -1 ? "" : tbl.className.match(/paginationcallback-([\S]+)/)[1];

                        // Replace "-" with "." to enable Object.method callbacks
                        cb = cb.replace("-", ".");

                        tablePaginater.tableInfo[tbl.id] = {
                                rowsPerPage:Number(tbl.className.match(/paginate-([0-9]+)/)[1]),
                                currentPage:cp,
                                totalRows:hook.getElementsByTagName('tr').length,
                                hook:hook,
                                maxPages:maxPages,
                                numPages:numPages,
                                rowStyle:tbl.className.search(/rowstyle-([\S]+)/) != -1 ? tbl.className.match(/rowstyle-([\S]+)/)[1] : false,
                                callback:cb || "paginationCallback"
                        };
                        
                        tablePaginater.showPage(tbl.id);
                        hook = null;
                };
        },
        calculateVisibleRows: function(hook) {
                var trs = hook.rows;
                var cnt = 0;
                var reg = /(^|\s)invisibleRow(\s|$)/;
                
                for(var i = 0, tr; tr = trs[i]; i++) {
                        if(tr.parentNode != hook || tr.getElementsByTagName("th").length || (tr.parentNode && tr.parentNode.tagName.toLowerCase().search(/thead|tfoot/) != -1)) continue;

                        if(tr.className.search(reg) == -1) {
                                cnt++;
                        };
                };
                return cnt;
        },
        createButton: function(details, ul, pseudo) {
                var li   = document.createElement("li");
                var but  = document.createElement(pseudo ? "div" : "a");
                var span = document.createElement("span");

                if(!pseudo) { but.href = "#"; };
                if(!pseudo) { but.title = details.title; };
                
                but.className = details.className;

                ul.appendChild(li);
                li.appendChild(but);
                but.appendChild(span);
                span.appendChild(document.createTextNode(details.text));

                if(!pseudo) { li.onclick = but.onclick = tablePaginater.buttonClick; };
                if(!pseudo && details.id) { but.id = details.id; };
                
                li = but = span = null;
        },
        removePagination: function(tableId) {
                var wrapT = document.getElementById(tableId + "-fdtablePaginaterWrapTop");
                var wrapB = document.getElementById(tableId + "-fdtablePaginaterWrapBottom");
                if(wrapT) { wrapT.parentNode.removeChild(wrapT); };
                if(wrapB) { wrapB.parentNode.removeChild(wrapB); };
        },
        buildPagination: function(tblId) {
                if(!(tblId in tablePaginater.tableInfo)) { return; };

                tablePaginater.removePagination(tblId);

                var details = tablePaginater.tableInfo[tblId];
                
                if(details.numPages < 2) return;
                
                function resolveText(txt, curr) {
                        curr = curr || details.currentPage;
                        return txt.replace("%p", curr).replace("%t", details.numPages);
                };

                if(details.maxPages) {
                        findex = Math.max(0, Math.floor(Number(details.currentPage - 1) - (Number(details.maxPages - 1) / 2)));
                        lindex = findex + Number(details.maxPages);
                        if(lindex > details.numPages) {
                                lindex = details.numPages;
                                findex = Math.max(0, details.numPages - Number(details.maxPages));
                        };
                } else {
                        findex = 0;
                        lindex = details.numPages;
                };
                

                var wrapT = document.createElement("div");
                wrapT.className = "fdtablePaginaterWrap";
                wrapT.id = tblId + "-fdtablePaginaterWrapTop";

                var wrapB = document.createElement("div");
                wrapB.className = "fdtablePaginaterWrap";
                wrapB.id = tblId + "-fdtablePaginaterWrapBottom";

                // Create list scaffold
                var ulT = document.createElement("ul");
                ulT.id  = tblId + "-tablePaginater";

                var ulB = document.createElement("ul");
                ulB.id  = tblId + "-tablePaginaterClone";
                ulT.className = ulB.className = "fdtablePaginater";

                // Add to the wrapper DIVs
                wrapT.appendChild(ulT);
                wrapB.appendChild(ulB);

                // FIRST (only created if maxPages set)
                if(details.maxPages) {
                        tablePaginater.createButton({title:tablePaginater.text[0], className:"first-page", text:"\u00ab"}, ulT, !findex);
                        tablePaginater.createButton({title:tablePaginater.text[0], className:"first-page", text:"\u00ab"}, ulB, !findex);
                };
                
                // PREVIOUS (only created if there are more than two pages)
                if(details.numPages > 2) {
                        tablePaginater.createButton({title:resolveText(tablePaginater.text[1], details.currentPage-1), className:"previous-page", text:"\u2039", id:tblId+"-previousPage"}, ulT, details.currentPage == 1);
                        tablePaginater.createButton({title:resolveText(tablePaginater.text[1], details.currentPage-1), className:"previous-page", text:"\u2039", id:tblId+"-previousPageC"}, ulB, details.currentPage == 1);
                };
                
                // NUMBERED
                for(var i = findex; i < lindex; i++) {
                        tablePaginater.createButton({title:resolveText(tablePaginater.text[4], i+1), className:i != (details.currentPage-1) ? "page-"+(i+1) : "currentPage page-"+(i+1), text:(i+1), id:i == (details.currentPage-1) ? tblId + "-currentPage" : ""}, ulT);
                        tablePaginater.createButton({title:resolveText(tablePaginater.text[4], i+1), className:i != (details.currentPage-1) ? "page-"+(i+1) : "currentPage page-"+(i+1), text:(i+1), id:i == (details.currentPage-1) ? tblId + "-currentPageC" : ""}, ulB);
                };

                // NEXT (only created if there are more than two pages)
                if(details.numPages > 2) {
                        tablePaginater.createButton({title:resolveText(tablePaginater.text[2], details.currentPage + 1), className:"next-page", text:"\u203a", id:tblId+"-nextPage"}, ulT, details.currentPage == details.numPages);
                        tablePaginater.createButton({title:resolveText(tablePaginater.text[2], details.currentPage + 1), className:"next-page", text:"\u203a", id:tblId+"-nextPageC"}, ulB, details.currentPage == details.numPages);
                };
                
                // LAST (only created if maxPages set)
                if(details.maxPages) {
                        tablePaginater.createButton({title:resolveText(tablePaginater.text[3], details.numPages), className:"last-page", text:"\u00bb"}, ulT, lindex == details.numPages);
                        tablePaginater.createButton({title:resolveText(tablePaginater.text[3], details.numPages), className:"last-page", text:"\u00bb"}, ulB, lindex == details.numPages);
                };
                
                // DOM inject wrapper DIVs (FireFox Bug: this has to be done here if you use display:table)
                if(document.getElementById(tblId+"-paginationListWrapTop")) {
                        document.getElementById(tblId+"-paginationListWrapTop").appendChild(wrapT);
                } else {
                        document.getElementById(tblId).parentNode.insertBefore(wrapT, document.getElementById(tblId));
                };

                if(document.getElementById(tblId+"-paginationListWrapBottom")) {
                        document.getElementById(tblId+"-paginationListWrapBottom").appendChild(wrapB);
                } else {
                        document.getElementById(tblId).parentNode.insertBefore(wrapB, document.getElementById(tblId).nextSibling);
                };
        },
        // The tableSort script uses this function to redraw.
        redraw: function(tableid, identical) {
                if(!tableid || !(tableid in fdTableSort.tableCache) || !(tableid in tablePaginater.tableInfo)) { return; };
                
                var dataObj     = fdTableSort.tableCache[tableid];
                var data        = dataObj.data;
                var len1        = data.length;
                var len2        = len1 ? data[0].length - 1 : 0;
                var hook        = dataObj.hook;
                var colStyle    = dataObj.colStyle;
                var rowStyle    = dataObj.rowStyle;
                var colOrder    = dataObj.colOrder;
                
                var page        = tablePaginater.tableInfo[tableid].currentPage - 1;
                var d1          = tablePaginater.tableInfo[tableid].rowsPerPage * page;
                var d2          = Math.min(tablePaginater.tableInfo[tableid].totalRows, d1 + tablePaginater.tableInfo[tableid].rowsPerPage);

                var cnt         = 0;
                var rs          = 0;
                var reg         = /(^|\s)invisibleRow(\s|$)/;
                
                var tr, tds, cell, pos;

                for(var i = 0; i < len1; i++) {
                        tr = data[i][len2];
                        
                        if(colStyle) {
                                tds = tr.cells;
                                for(thPos in colOrder) {
                                        if(!colOrder[thPos]) tablePaginater.removeClass(tds[thPos], colStyle);
                                        else tablePaginater.addClass(tds[thPos], colStyle);
                                };
                        };
                        
                        if(tr.className.search(reg) != -1) {
                                continue;
                        };
                        
                        if(!identical) {
                                cnt++;

                                if(cnt > d1 && cnt <= d2) {
                                        if(rowStyle) {
                                                if(rs++ & 1) tablePaginater.addClass(tr, rowStyle);
                                                else tablePaginater.removeClass(tr, rowStyle);
                                        };
                                        tr.style.display = "";
                                } else {
                                        tr.style.display = "none";
                                };

                                // Netscape 8.1.2 requires the removeChild call or it freaks out, so add the line if you want to support this browser
                                // hook.removeChild(tr);
                                hook.appendChild(tr);
                        };
                };

                tr = tds = hook = null;
        },
        showPage: function(tblId, pageNum) {
                if(!(tblId in tablePaginater.tableInfo)) { return; };

                var page = !pageNum ? tablePaginater.tableInfo[tblId].currentPage - 1 : pageNum - 1;

                var d1  = tablePaginater.tableInfo[tblId].rowsPerPage * page;
                var d2  = Math.min(tablePaginater.tableInfo[tblId].totalRows, d1 + tablePaginater.tableInfo[tblId].rowsPerPage);
                var trs = tablePaginater.tableInfo[tblId].hook.rows;
                var cnt = 0;
                var rc  = 0;
                var len = trs.length;
                var rs  = tablePaginater.tableInfo[tblId].rowStyle;
                var reg = /(^|\s)invisibleRow(\s|$)/;
                
                for(var i = 0; i < len; i++) {
                        if(trs[i].getElementsByTagName("th").length || (trs[i].parentNode && trs[i].parentNode.tagName.toLowerCase().search(/thead|tfoot/) != -1)) continue;

                        if(trs[i].className.search(reg) != -1) {
                                continue;
                        };

                        cnt++;
                        
                        if(cnt > d1 && cnt <= d2) {
                                if(rs) {
                                        if(rc++ & 1) {
                                                tablePaginater.addClass(trs[i], rs);
                                        } else {
                                                tablePaginater.removeClass(trs[i], rs);
                                        }
                                };
                                trs[i].style.display = "";
                        } else {
                                trs[i].style.display = "none";
                        };
                };

                tablePaginater.buildPagination(tblId);
                tablePaginater.callback(tblId);
        },
        callback: function(tblId) {
                var func;
                if(tablePaginater.tableInfo[tblId].callback.indexOf(".") != -1) {
                        var split = tablePaginater.tableInfo[tblId].callback.split(".");
                        func = window;
                        for(var i = 0, f; f = split[i]; i++) {
                                if(f in func) {
                                        func = func[f];
                                } else {
                                        func = "";
                                        break;
                                };
                        };
                } else if(tablePaginater.tableInfo[tblId].callback in window) {
                        func = window[tablePaginater.tableInfo[tblId].callback];
                };

                if(typeof func == "function") {
                        func(tblId);
                };

                func = null;
        },
        buttonClick: function(e) {
                e = e || window.event;

                var a = this.tagName.toLowerCase() == "a" ? this : this.getElementsByTagName("a")[0];

                if(a.className.search("currentPage") != -1) return false;

                var ul = this;
                while(ul.tagName.toLowerCase() != "ul") ul = ul.parentNode;

                var tblId = ul.id.replace("-tablePaginaterClone","").replace("-tablePaginater", "");

                tablePaginater.tableInfo[tblId].lastPage = tablePaginater.tableInfo[tblId].currentPage;
                
                var showPrevNext = 0;
                
                if(a.className.search("previous-page") != -1) {
                        tablePaginater.tableInfo[tblId].currentPage = tablePaginater.tableInfo[tblId].currentPage > 1 ? tablePaginater.tableInfo[tblId].currentPage - 1 : tablePaginater.tableInfo[tblId].numPages;
                        showPrevNext = 1;
                } else if(a.className.search("next-page") != -1) {
                        tablePaginater.tableInfo[tblId].currentPage = tablePaginater.tableInfo[tblId].currentPage < tablePaginater.tableInfo[tblId].numPages ? tablePaginater.tableInfo[tblId].currentPage + 1 : 1;
                        showPrevNext = 2;
                } else if(a.className.search("first-page") != -1) {
                        tablePaginater.tableInfo[tblId].currentPage = 1;
                } else if(a.className.search("last-page") != -1) {
                        tablePaginater.tableInfo[tblId].currentPage = tablePaginater.tableInfo[tblId].numPages;
                } else {
                        tablePaginater.tableInfo[tblId].currentPage = parseInt(a.className.match(/page-([0-9]+)/)[1]) || 1;
                };

                tablePaginater.showPage(tblId);

                // Focus on the appropriate button (previous, next or the current page)
                // I'm hoping screen readers are savvy enough to indicate the focus event to the user
                if(showPrevNext == 1) {
                        var elem = document.getElementById(ul.id.search("-tablePaginaterClone") != -1 ? tblId + "-previousPageC" : tblId + "-previousPage");
                } else if(showPrevNext == 2) {
                        var elem = document.getElementById(ul.id.search("-tablePaginaterClone") != -1 ? tblId + "-nextPageC" : tblId + "-nextPage");
                } else {
                        var elem = document.getElementById(ul.id.search("-tablePaginaterClone") != -1 ? tblId + "-currentPageC" : tblId + "-currentPage");
                };
                
                if(elem && elem.tagName.toLowerCase() == "a") { elem.focus(); };

                if(e.stopPropagation) {
                        e.stopPropagation();
                        e.preventDefault();
                };

                /*@cc_on
                @if(@_win32)
                e.cancelBubble = true;
                e.returnValue  = false;
                @end
                @*/
                return false;
        },
        onUnLoad: function(e) {
                var tbl, lis, pagination, uls;
                for(tblId in tablePaginater.tableInfo) {
                        uls = [tblId + "-tablePaginater", tblId + "-tablePaginaterClone"];
                        for(var z = 0; z < 2; z++) {
                                pagination = document.getElementById(uls[z]);
                                if(!pagination) { continue; };
                                lis = pagination.getElementsByTagName("li");
                                for(var i = 0, li; li = lis[i]; i++) {
                                        li.onclick = null;
                                        if(li.getElementsByTagName("a").length) { li.getElementsByTagName("a")[0].onclick = null; };
                                };
                        };
                };
        }
};

tablePaginater.addEvent(window, "load",   tablePaginater.init);
tablePaginater.addEvent(window, "unload", tablePaginater.onUnLoad);
