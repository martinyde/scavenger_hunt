/*
let timeLeft = document.getElementById("race-timeleft").getAttribute("data-timeleft");
let countdownWrapper = document.getElementById("countdown");
let timeObject = new Date();
let start = new Date(timeObject.getTime() + (timeLeft * 1000));
if (start > timeObject) {
  let downloadTimer = setInterval(function(){
    let current = new Date();
    let count = +start - +current;
    console.log(count)

    let s = Math.floor((count /  1000)) % 60;
    let m = Math.floor((count / 60000)) % 60;
    let h = Math.floor((count / 3600000)) % 60;

    countdownWrapper.textContent = addZero(h) + ":" + addZero(m) + ":" + addZero(s);

    if (count < 0) {
      countdownWrapper.textContent = '00:00:00';
      clearInterval(downloadTimer);
    }
  },1000);
}
else {
  countdownWrapper.textContent = '00:00:00';
}

function addZero(number) {
  return (number < 10 ? '0' : '') + number;
}

 */

