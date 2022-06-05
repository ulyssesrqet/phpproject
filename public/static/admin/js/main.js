(function (window, $, toastr) {
  window.main = {
    token: '',					// 保存令牌
    toastr: toastr,
    init: function (opt) {
      $.extend(this, opt);		// 将传入的opt对象合并到自身对象中
      toastr.options.positionClass = 'toast-top-center';
      $(window).on('popstate', function () {
        window.location.reload();
      });    
      return this;
    },
    ajax: function (opt, success, error) {
      opt = (typeof opt === 'string') ? { url: opt } : opt;
      var that = this;
      var options = {
        success: function (data, status, xhr) {
          that.hideLoading();
          if (!data) {
            toastr.error('请求失败，请重试。');
          } else if (data.code === 0) {
            toastr.error(data.msg);
            error && error(data);
          } else {
            success && success(data);
          }
          opt.success && opt.success(data, status, xhr);
        },
        error: function (xhr, status, err) {
          that.hideLoading();
          toastr.error('请求失败，请重试。');
          opt.error && opt.error(xhr, status, err);
        }
      };
      that.showLoading();
      $.ajax($.extend({}, opt, options));
    },
    ajaxPost: function (opt, success, error) {
      opt = (typeof opt === 'string') ? { url: opt } : opt;
      var that = this;
      var callback = opt.success;
      opt.type = 'POST';
      opt.headers = opt.headers ? opt.headers : {};			// 确保opt.headers对象存在
      opt.headers['X-CSRF-TOKEN'] = that.token;					// 将令牌放入请求头
      opt.success = function (data, status, xhr) {
        that.token = xhr.getResponseHeader('X-CSRF-TOKEN');		// 将响应头中的令牌保存
        if (data && data.code === 1) {
          toastr.success(data.msg);
        }
        callback && callback(data, status, xhr);
      };
      that.ajax(opt, success, error);
    },
    ajaxForm: function (selector, success, error) {
      var form = $(selector);
      var that = this;
      form.submit(function (e) {
        e.preventDefault();
        that.ajaxPost({
          url: form.attr('action'),
          data: new FormData(form.get(0)),
          contentType: false,
          processData: false
        }, success, error);
      });
    },
    showLoading: function () {
      $('.main-loading').show();
    },
    hideLoading: function () {
      $('.main-loading').hide();
    },
    layout: function () {
      var that = this;
      $('.main-sidebar-collapse-btn').click(function () {
        $(this).parent().find('.nav').slideToggle(200);
        $(this).parent().toggleClass('main-sidebar-collapse').siblings().addClass('main-sidebar-collapse').find('.nav').slideUp(200);
        return false;
      });
      var $menuLink = $('.main-menu a');
      $menuLink.click(function () {
        toastr.clear();
        var $this = $(this);
        if ($this.hasClass('main-sidebar-collapse-btn')) {
          return false;
        }
        that.content($this.attr('href'), function () {
          $menuLink.removeClass('active');
          $this.addClass('active');
          $this.parents('.collapse').collapse('hide');
        });
        return false;
      });
      $('.main-content').on('click', 'a:not([target])', function() {
        that.content($(this).attr('href'));
        return false;
      });
      $('.j-layout-pwd').click(function() {
        that.content($(this).attr('href'));
        return false;
      });    
    },
    content: function(url, success) {
      var $content = $('.main-content');
      this.ajax(url, function(data) {
        $content.html(data);
        $content.parents('.main-container').animate({scrollTop: '0px'}, 'fast');
        window.history.pushState(null, null, url);
        success && success(data);
      });
    },
    menuActive: function(name) {
      var menu = $('.main-menu');
      menu.find('a').removeClass('active');
      menu.find('a[data-name=\'' + name + '\']').addClass('active');
    },
    contentRefresh: function(success) {
      var url = window.location.href;
      var $content = $('.main-content');
      this.ajax({url: url}, function(data) {
        $content.html(data);
        success && success(data);
      });
    },
    loaded: {css: [], js: []},
    loadCSS: function(arr) {
      arr = (typeof arr === 'string') ? [arr] : arr;
      for (var i in arr) {
        if (this.loaded.css[arr[i]]) {
          return;
        }
        $('<link>').attr({rel: 'stylesheet', href: arr[i]}).appendTo('head');
        this.loaded.css[arr[i]] = true;
      }
    },
    loadJS: function(arr) {
      arr = (typeof arr === 'string') ? [arr] : arr;
      for (var i in arr) {
        if (this.loaded.js[arr[i]]) {
          return;
        }
        $('<script></script>').attr('src', arr[i]).appendTo('head');
        this.loaded.js[arr[i]] = true;
      }
    },
    modal: function(url, submit) {
      var that = this;
      var modal = $('.modal');
      that.ajax(url, function(data) {
        modal.find('.j-modal-content').html(data);
      });
      modal.find('.j-modal-submit').off('click').click(function() {
        if (submit(modal) !== false) {
          modal.modal('hide');
        }
      });
      modal.modal('show');
    }
  };
})(this, jQuery, toastr);