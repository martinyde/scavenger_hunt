import { Controller } from "@hotwired/stimulus";

/*
 * Stimulus controller triggered in timer.html.twig
 */
export default class extends Controller {
    connect() {
        let timeLeft = document
            .getElementById("race-timeleft")
            .getAttribute("data-timeleft");
        let duration = document
            .getElementById("race-timeleft")
            .getAttribute("data-duration");
        let raceIsActive = document
            .getElementById("race-timeleft")
            .getAttribute("data-race-state");
        let countdownWrapper = this.element;
        let timeObject = new Date();
        let start = new Date(timeObject.getTime() + timeLeft * 1000);
        if (start > timeObject && raceIsActive) {
            let downloadTimer = setInterval(function () {
                let current = new Date();
                let count = +start - +current;

                let s = Math.floor(count / 1000) % 60;
                let m = Math.floor(count / 60000) % 60;
                let h = Math.floor(count / 3600000) % 60;

                countdownWrapper.textContent =
                    addZero(h) + ":" + addZero(m) + ":" + addZero(s);

                if (count < 0) {
                    countdownWrapper.textContent = "00:00:00";
                    clearInterval(downloadTimer);
                }
            }, 1000);
        } else {
            let s = Math.floor(duration) % 60;
            let m = Math.floor(duration / 60) % 60;
            let h = Math.floor(duration / 3600) % 60;

            countdownWrapper.textContent =
                addZero(h) + ":" + addZero(m) + ":" + addZero(s);
        }

        function addZero(number) {
            return (number < 10 ? "0" : "") + number;
        }
    }
}
