import { Controller } from "@hotwired/stimulus";

/*
 * Stimulus controller triggered in timer.html.twig
 */
export default class extends Controller {
    connect() {
      const nav = this.element.querySelectorAll('.participant-tool-nav')
      nav.forEach((item) => {
        item.addEventListener('click', function(){
            toolShift()
          },
          false
        );
      });
    }
}

function toolShift() {
  let element = document.getElementById('participant-pass-list');
  if (element.classList.contains('show')) {
    element.classList.replace("show", "hide");
  }
  else {
    element.classList.replace("hide", "show");
  }
}