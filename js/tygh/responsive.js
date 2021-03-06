'use strict';

(function(_, $) {

    // ui module
    var ui = (function() {
        return {
            winWidth: function() {
                return $(window).width();
            },

            responsiveScroll: function() {
                $.ceEvent('on', 'ce.needScroll', function(opt) {

                    opt.need_scroll = false;
                    setTimeout(function() {
                        $.scrollToElm($('#' + opt.jelm.data('caScroll')));
                    }, 310);
                });
            },

            responsiveNotifications: function() {
                if(this.winWidth() <= 767) {
                    $.ceEvent('on', 'ce.notificationshow', function(notification) {
                        if($(notification).hasClass('cm-notification-content-extended')) {
                            $('body,html').scrollTop(0);
                        }
                    });
                }
            },

            responsiveTabs: function() {
                if(this.winWidth() <= 480) {

                    // conver tabs to accordion
                    $('.cm-j-tabs:not(.cm-j-tabs-disable-convertation)').each(function(index) {
                        var accordion = $('<div class="ty-accordion cm-accordion" id="accordion_id_' + index + '">');
                        var tabsContent = $('.cm-tabs-content');
                        var self = this;

                        // hide tabs
                        $(this).hide();
                        tabsContent.hide();

                        if(!$('#accordion_id_' + index).length) {

                            $(this).find('>ul>li').each(function() {
                                var id = $(this).attr('id');
                                var content = $('.cm-tabs-content > #content_' + id).show();

                                // rename tab id
                                $(this).attr('id', 'hidden_tab_' + id);

                                accordion.append('<h3 id="' + id + '">' + $(this).text() + '</h3>');
                                $(content).appendTo(accordion);
                            });

                            $(self).before(accordion);
                        }
                    });

                    $('.cm-accordion').ceAccordion('reinit', {heightStyle : "content"});

                    var active = _.anchor;
                    if(typeof active !== 'undefined' && $(active).length > 0) {
                        $(active).click();
                    }

                } else {

                    $('.cm-accordion').accordion('destroy');

                    $('.cm-accordion > div').each(function(index) {
                        $(this).hide();
                        $(this).appendTo($('.cm-tabs-content'));
                    });

                    $('.cm-accordion').remove();

                    // remove prefix
                    $('.cm-j-tabs>ul>li').each(function(){
                        var id = $(this).attr('id').replace('hidden_tab_','');
                        $(this).attr('id', id);

                        if($(this).hasClass('active')) {
                            $('#content_' + $(this).attr('id')).show();
                        }
                    });

                    $('.cm-j-tabs, .cm-tabs-content').show();
                }
            },

            responsiveMenu: function() {

                var whichEvent = ('ontouchstart' in document.documentElement ? "touchstart" : "click");;

                // FIXME Windows IE 8 doesn't have touch event
                if(_.isTouch && window.navigator.msPointerEnabled) {
                    whichEvent = 'click';
                }

                if($('.ty-menu__menu-btn').data('ca-responsive-menu') !== true) {

                    $('.ty-menu__menu-btn').on(whichEvent, function(e) {
                        var menu_elm = $('.cm-responsive-menu');
                        $(this).parent(menu_elm).find('.ty-menu__item').toggle();
                    });

                    $('.cm-responsive-menu-toggle').on(whichEvent,function (e) {
                        $(this).toggleClass('ty-menu__item-toggle-active');
                        $('.icon-down-open', this).toggleClass('icon-up-open');
                        $(this).parent().find('.cm-responsive-menu-submenu').first().toggleClass('ty-menu__items-show');
                    });

                    $('.ty-menu__menu-btn').data('ca-responsive-menu', true);
                }

                if(_.isTouch == false && ui.winWidth() >= 767) {
                    $('.cm-responsive-menu').on('mouseover mouseout', function(e) {
                        ui.detectMenuWidth(e);
                    });
                }

            },

            responsiveMenuLargeTouch: function(e) {
                var elm = $(e.target);
                var menuWidth = $('.cm-responsive-menu').width();
                if (ui.winWidth() >= 767 && e.type == 'touchstart') {
                    if (elm.hasClass('cm-menu-item-responsive') || elm.closest('.cm-menu-item-responsive').length) {
                        var menuItem = elm.hasClass('cm-menu-item-responsive') ? elm : elm.closest('.cm-menu-item-responsive');
                        if (!menuItem.hasClass('is-hover-menu')) {
                            //e.preventDefault();
                            $('.cm-menu-item-responsive').removeClass('is-hover-menu');
                            menuItem.addClass('is-hover-menu');
                        }
                    }

                    var subMenu = $('.ty-menu__submenu-items');
                    if (subMenu.is(':visible') && !elm.closest('.cm-menu-item-responsive').length) {
                        $('.cm-menu-item-responsive').removeClass('is-hover-menu');
                    }

                } else {
                    $('.cm-menu-item-responsive').removeClass('is-hover-menu');
                }

                ui.detectMenuWidth(e);
            },

            detectMenuWidth: function(e) {
                var elm = $(e.target);
                var menuElm = elm.parents(".cm-responsive-menu");

                if(menuElm.hasClass("ty-menu-vertical") == false) {
                    var menuWidth = menuElm.width();
                    var menuItemElm = elm.closest('.cm-menu-item-responsive');
                    $('.ty-menu__submenu-to-right').removeClass('ty-menu__submenu-to-right');

                    if(menuItemElm) {
                        var submenu = $('.cm-responsive-menu-submenu', menuItemElm);
                        if(submenu.length) {
                            var position = submenu.position().left + submenu.width();
                            if(position > menuWidth) {
                                submenu.parent().addClass('ty-menu__submenu-to-right');
                            }
                        }

                    }
                }
            },

            responsiveTables: function(e) {

                var tables = $('.ty-table');
                var objSize = function(obj) {
                    var count = 0;

                    if (typeof obj == "object") {

                        if (Object.keys) {
                            count = Object.keys(obj).length;
                        } else if (window._) {
                            count = _.keys(obj).length;
                        } else if (window.$) {
                            count = $.map(obj, function() { return 1; }).length;
                        } else {
                            for (var key in obj) if (obj.hasOwnProperty(key)) count++;
                        }

                    }

                    return count;
                };

                if(this.winWidth() <= 767) {
                    tables.each(function() {
                        var thTexts = [];
                        var tbData = {};
                        var sbTables = {};

                        var num = 1;
                        $(this).find('.ty-table').each(function() {
                            $(this).parent().attr('data-ca-has-sub-table', num);
                            sbTables[num] = $(this).detach();
                            num++;
                        });

                        var irow = 0
                        $(this).find('tr').each(function() {
                            var thRow = [];
                            $(this).find('th').each(function() {
                                thRow.push($(this).text());
                            });
                            if (thRow.length == 1) {
                                thTexts = thTexts.concat(thRow);
                                var icol = 0;
                                $(this).find('td').each(function() {
                                    tbData[icol] = tbData[icol] || {};
                                    tbData[icol][irow] = $(this).html();
                                    icol++;
                                });
                            } else if (thRow.length > 1) {
                                thTexts = thRow;
                            } else {
                                tbData[irow] = {};
                                var icol = 0;
                                $(this).find('td').each(function() {
                                    tbData[irow][icol] = $(this).html();
                                    icol++;
                                });
                            }
                            irow++;
                        });
                        if (thTexts.length > 0) {
                            var newHTML = '';
                            for (ikey in tbData) {
                                newHTML += '<tr>';
                                var show_header = false;
                                if (thTexts.length == objSize(tbData[ikey])) {
                                    show_header = true;
                                }
                                for (jkey in tbData[ikey]) {
                                    if (show_header) {
                                        var header = '<div class="ty-table__responsive-header">' + thTexts[jkey] + '</div>';
                                    } else {
                                        var header = '';
                                    }
                                    newHTML += '<td>' + header + '<div class="ty-table__responsive-content">' + tbData[ikey][jkey] + '</div></td>';
                                }
                                newHTML += '</tr>';
                            }
                            $(this).html(newHTML);
                        }
//                         $(this).find('th').each(function() {
//                             thTexts.push($(this).text());
//                             $(this).remove();
//                         });
//
//                         $(this).find('tr:not(.ty-table__no-items)').each(function() {
//                             if (!$.trim($(this).html())) {
//                                 $(this).remove();
//                             }
//                             $(this).find('td:not(.ty-table-disable-convertation)').each(function(index) {
//                                 var $elm = $(this);
//
//                                 if($elm.find('.ty-table__responsive-content').length == 0) {
//                                     $elm.wrapInner('<div class="ty-table__responsive-content"></div>');
//                                     $elm.prepend('<div class="ty-table__responsive-header">' + thTexts[index] + '</div>');
//                                 }
//                             });
//
//                         });

                        $(this).find('[data-ca-has-sub-table]').each(function() {
                            $(this).append(sbTables[$(this).data('caHasSubTable')]);

                            $(this).removeAttr('data-ca-has-sub-table');
                        });

                    });
                }
            },

            resizeDialog: function() {
                var self = this;
                var dlg = $('.ui-dialog:visible');

                if ($(dlg).length) {
                    $('.ui-widget-overlay').css({
                        'min-height': $(window).height()
                    });

                    // TennisHouse
                    $(dlg).css({
                        'position':'absolute',
                        'width': $(window).width() - 20,
                        'max-height': 'none',
                        'height': 'auto',
                        'margin-bottom': '10px'
                    });

                    var params = typeof($(dlg).find('.ui-dialog-content').data('params')) !== 'undefined' ? $(dlg).find('.ui-dialog-content').data('params') : {};

                    if (!params.keepInPlace) {
                        if (typeof(params.parentId) !== 'undefined' && typeof($('#' + params.parentId).offset()) !== 'unefined') {
                            $(dlg).css({
                                'left': '10px',
                                'top': $('#' + params.parentId).offset().top + 'px'
                            });
                        } else {
                            $(dlg).css({
                                'left': '10px',
                                'top': '10px'
                            });
                        }
                    } else {
                        if (typeof(params.parentId) !== 'undefined' && typeof($('#' + params.parentId).offset()) !== 'unefined') {
                            left_padding = $('#' + params.parentId).offset().left - 10;
                            $(dlg).css({
                                'left': '-' + left_padding + 'px',
                                'top': '10px'
                            });
                        } else {
                            $(dlg).css({
                                'left': '0',
                                'top': '0'
                            });
                        }
                    }
                    $('body,html').scrollTop($(dlg).offset().top - 10);
                    // TennisHouse

                    // calculate title width
                    $(dlg).find('.ui-dialog-title').css({
                        'width': $(window).width() - 80
                    });

                    $(dlg).find('.ui-dialog-content').css({
                        'height': 'auto',
                        'max-height': 'none'
                    });

                    // $(dlg).find('.object-container').css({
                    //     'height': 'auto'
                    // });

                    $(dlg).find('.buttons-container').css({
                        'position':'relative',
                        'top': 'auto',
                        'left': '0px',
                        'right': '0px',
                        'bottom': '0px',
                        'width': 'auto'
                    });
                }
            },

            responsiveDialog: function() {
                var self = this;
                $.ceEvent('on', 'ce.dialogshow', function() {
                    if(self.winWidth() <= 767) {
                        self.resizeDialog();
                    }
                });
            }
        }

    })();

    // Init
    $(document).ready(function(){

        $(window).resize(function(e){
            ui.winWidth();
            ui.responsiveTables();
        });

        window.addEventListener('orientationchange', function() {
            if(ui.winWidth() <= 767) {
                ui.resizeDialog();
            }
            $.ceDialog('get_last').ceDialog('reload');
        }, false);

        ui.responsiveDialog();

        // notifications
        ui.responsiveNotifications();

        // responsive tables
        ui.responsiveTables();

        $.ceEvent('on', 'ce.ajaxdone', function() {
            ui.responsiveTables();
            ui.responsiveMenu();

            if(ui.winWidth() <= 767) {
                ui.resizeDialog();
            }
        });

        // Menu init
        ui.responsiveMenu();
        $('.cm-responsive-menu').on('touchstart', function(e) {
            ui.responsiveMenuLargeTouch(e);
        });

    });

     // tabs
    $.ceEvent('on', 'ce.tab.init', function() {
        $(window).resize(function(e){
            ui.responsiveTabs();
        });

        ui.responsiveTabs();
        ui.responsiveScroll();
    });



}(Tygh, Tygh.$));
