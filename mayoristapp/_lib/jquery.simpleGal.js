/*
 * simpleGal -v0.0.1
 * A simple image gallery plugin.
 * https://github.com/steverydz/simpleGal
 * 
 * Made by Steve Rydz
 * Under MIT License
 */
(function($){

  $.fn.extend({

    simpleGal: function (options) {

      var defaults = {
        mainImage: ".placeholder"
      };

      options = $.extend(defaults, options);

      return this.each(function () {

        var thumbnail = $(this).find("a"),
            mainImage = $(options.mainImage);
//            mainImage = $(this).siblings().find(options.mainImage);
            //alert(options.mainImage);
        thumbnail.on("click", function (e) {
            e.preventDefault();
            $( ".thumbs-list li a" ).each(function() {
                $(this).attr("class","");
            });
            $(this).attr("class","active");
            var galleryImage = $(this).attr("href");          
            mainImage.attr("src", galleryImage);
        });

      });

    }

  });

})(jQuery);
