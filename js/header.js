const headerId = "site-header";
const logoId = "site-header-logo";

$(document).ready(() => {
  main();
});

function main() {
  const header = $("#" + headerId);
  const logo = $("#" + logoId);

  $(window).scroll(() => {
    if ($(window).scrollTop() >= 80 && !header.hasClass("site-header-scroll")) {
      header.addClass("site-header-scroll");
      collapseSiteLogo(logo);
    } else if ($(window).scrollTop() < 80 && header.hasClass("site-header-scroll")) {
      header.removeClass("site-header-scroll");
      expandSiteLogo(logo);
    }
  });
}

function collapseSiteLogo(logo) {
  const smallLogoPath = logo.attr("src").replace("oregonflora-logo.png", "oregonflora-logo-sm.png");
  logo.attr("src", smallLogoPath);
}

function expandSiteLogo(logo) {
  const lgLogoPath = logo.attr("src").replace("oregonflora-logo-sm.png", "oregonflora-logo.png");
  logo.attr("src", lgLogoPath);
}
