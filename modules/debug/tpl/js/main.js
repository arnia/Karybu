(function($) {

    $(function() {

        $(document).keypress("d", function(e) {
            if (e.shiftKey) {
                $.dToolbar('toggle');
            }
        });

        if ($.dToolbar().data('state')) {
            $.dToolbar('changeState', $.dToolbar().data('state'));
        } else {
            $.dToolbar('changeState', 'full');
        }

        $.dToolbar('toggleIcon').on('click', function() {
            $.dToolbar('toggle');
        });

        $.dToolbar('closeIcon').on('click', function() {
            $.dToolbar('close');
            // todo arata patratel in dreapta jos
        });

        $.dToolbar('tabs').on('click', function(e) {
            var index = $(this).data('debug-index');
            var contentElement = $.dToolbar('getContent', index);
            if (!contentElement.is(':visible')) {
                $.dToolbar('showTab', $(this));
                $.dToolbar('maximize');
            }
            e.preventDefault()
        });

        var mustBeShown;
        if (!$.dToolbar('tabs').hasClass('active')) {
            mustBeShown = $.dToolbar('tab', 0)
        } else {
            mustBeShown = $.dToolbar('tabs').filter('.active');
        }
        $.dToolbar('showTab', mustBeShown, true);

        $('.queries span.time', $.dToolbar()).stampToTime();
        $('.queries li').tooltip();
    });

    var methods = {
        get : function( options ) {
            return $('#debug-toolbar');
        },
        tab : function( i ) {
            var tabs = $.dToolbar('tabs');
            return i != null ? tabs.eq(i) : tabs;
        },
        tabs : function() {
            return $('#debug-tabs', $.dToolbar()).find('li');
        },
        tabContent : function( i ) {

        },
        getActiveTab : function() {
            var tabs = $.dToolbar('tabs');
            return tabs.find('li.active');
        },
        content : function() {
            return $('.debug-content', $.dToolbar());
        },
        statusBar: function() {
            return $('.status', $.dToolbar());
        },
        toggleIcon : function() {
            return $('a.toggle', $.dToolbar());
        },
        closeIcon : function() {
            return $('a.hide', $.dToolbar());
        },
        isUp : function() {
            return $.dToolbar('content').is(':visible');
        },
        getContent : function(index) {
            return $('#debug_tab_' + index, $.dToolbar());
        },
        checkBottomPadding : function() {
            if ($.dToolbar().data('padding-added') == 'da') {
                $('body').css('padding-bottom', '-=' + $('.debug-nav', $.dToolbar()).height() + 'px');
                $.dToolbar().removeData('padding-added');
            }
            else {
                $.dToolbar().data('padding-added', 'da');
                $('body').css('padding-bottom', '+=' + $('.debug-nav', $.dToolbar()).height() + 'px');
            }
        },

        msg1 : function(msg) {
            var container = $('.debug-nav p.pull-right', $.dToolbar());
            if (msg != null) container.text(msg);
            return container;
        },
        msg2 : function(msg) {
            var container = $('div.status > p', $.dToolbar());
            if (msg != null) container.text(msg);
            return container;
        },

        toggle : function() {
            if ($.dToolbar('isUp')) $.dToolbar('minimize');
            else $.dToolbar('maximize');
            return $.dToolbar();
        },
        minimize : function() {
            $.dToolbar('content').hide();
            $.dToolbar('statusBar').hide();
            $.dToolbar('toggleIcon').removeClass('up');
            $.dToolbar('checkBottomPadding');
            $.dToolbar('ajax', { state: 'minimized' });
            return $.dToolbar();
        },
        maximize : function() {
            $.dToolbar('content').show();
            $.dToolbar('statusBar').show();
            $.dToolbar('toggleIcon').addClass('up');
            $.dToolbar('ajax', { state: 'full' });
            return $.dToolbar();
        },
        close : function() {
            $.dToolbar('checkBottomPadding');
            $.dToolbar('ajax', {
                state: 'closed'
            });
            return $.dToolbar().hide();
        },
        open : function() {
            return $.dToolbar().show();
        },
        setTabActive : function(tab) {
            $.dToolbar('tabs').removeClass('active');
            tab.addClass('active');
        },
        showTab : function(tab, noAjax) {
            $.dToolbar('setTabActive', tab);
            contentElement = $('#debug_tab_' + tab.data('debug-index'), $.dToolbar());
            $('.tab-content', $.dToolbar()).hide();
            contentElement.show();
            if (!noAjax) {
                $.dToolbar('ajax', { tab: tab.data('debug-index') });
            }
        },
        changeState : function(state) {
            if (state == 'full') {
                $.dToolbar().show();
            }
            else if (state == 'minimized') {
                $.dToolbar('minimize').show();
            }
            else if (state == 'closed') {
                $.dToolbar('close').show();
            }
            else $.error('wrong state ' + state);
            return $.dToolbar();
        },

        ajax : function(data) {
            $.exec_json('debug.procDebugSaveToolbarSettings', data, function(ret) {
                if (ret.message != 'success') {
                    $.dToolbar('msg1', ret.message);
                }
            });
        }
    };

    $.dToolbar = function( method ) {
        // Method calling logic
        if ( methods[method] ) {
            return methods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ));
        } else if ( typeof method === 'object' || ! method ) {
            return methods.get.apply( this, arguments );
        } else {
            $.error( 'Method ' +  method + ' does not exist on jQuery.dToolbar' );
        }
    };

    $.fn.stampToTime = function() {
        return this.each(function(){
            var timestamp = $(this).text();
            var time = new Date(timestamp * 1000);
            var pad = '00';
            var n = time.getHours();
            var h = (pad+n).slice(-pad.length);
            n = time.getMinutes();
            var m = (pad+n).slice(-pad.length);
            n = time.getSeconds();
            var s = (pad+n).slice(-pad.length);
            n = time.setMilliseconds(2);
            var ms = (pad+n).slice(-pad.length);
            $(this).text(h + ':' + m + ':' + s + ':' + ms);
        });
    }

})(jQuery);