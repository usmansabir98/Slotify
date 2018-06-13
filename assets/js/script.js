let currentPlaylist = [];
let shufflePlaylist = [];
let tempPlaylist = [];
let audioElement;
let mouseDown = false;
let currentIndex = 0;
let repeat = false;
let shuffle = false;
let userLoggedIn;

function openPage(url) {

	if(url.indexOf("?") == -1) {
		url = url + "?";
	}

	var encodedUrl = encodeURI(url + "&userLoggedIn=" + userLoggedIn);
	$("#mainContent").load(encodedUrl);
	$("body").scrollTop(0);
	history.pushState(null,null, url);

}

function formatTime(sec){
	const time = Math.round(sec);
	const minutes = Math.floor(time/60);
	const seconds = time - (minutes*60);
	const extraZero = (seconds<10)?"0":"";
	return `${minutes}:${extraZero}${seconds}`;
}

function updateTimeProgressBar(audio) {
	document.querySelector(".progressTime.current").textContent = formatTime(audio.currentTime);
	document.querySelector(".progressTime.remaining").textContent = formatTime(audio.duration - audio.currentTime);

	const progress = audio.currentTime / audio.duration * 100;
	document.querySelector(".playbackBar .progress").style.width = progress + "%";
}

function updateVolumeProgressBar(audio) {
	const volume = audio.volume * 100;
	document.querySelector(".volumeBar .progress").style.width = volume + "%";
}

function playFirstSong(){
	setTrack(tempPlaylist[0], tempPlaylist, true);
}


class Audio{
	constructor(){
		this.currentlyPlaying = 0;
		this.audio = document.createElement('audio');

		this.audio.addEventListener('ended', function(){
			nextSong();
		});

		this.audio.addEventListener('canplay', function(){
			document.querySelector('.progressTime.remaining').textContent = formatTime(this.duration);
		});

		this.audio.addEventListener("timeupdate", function(){
			if(this.duration) {
				updateTimeProgressBar(this);
			}
		});

		this.audio.addEventListener("volumechange", function(){
			updateVolumeProgressBar(this);
		});
	}


	setTrack(track){
		this.currentlyPlaying = track;
		this.audio.src = track.path;
	}

	setTime(seconds) {
		this.audio.currentTime = seconds;
	}

	play(){
		this.audio.play();
	}

	pause(){
		this.audio.pause();
	}
}