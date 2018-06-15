let currentPlaylist = [];
let shufflePlaylist = [];
let tempPlaylist = [];
let audioElement;
let mouseDown = false;
let currentIndex = 0;
let repeat = false;
let shuffle = false;
var userLoggedIn;
var timer;

$(document).click(function(click) {
	var target = $(click.target);

	if(!target.hasClass("item") && !target.hasClass("optionsButton")) {
		hideOptionsMenu();
	}
});

$(window).scroll(function() {
	hideOptionsMenu();
});

$(document).on("change", "select.playlist", function() {
	var select = $(this);
	var playlistId = select.val();
	var songId = select.prev(".songId").val();

	$.post("includes/handlers/ajax/addToPlaylist.php", { playlistId: playlistId, songId: songId})
	.done(function(error) {

		if(error != "") {
			alert(error);
			return;
		}

		hideOptionsMenu();
		select.val("");
	});
});

function removeFromPlaylist(button, playlistId){
	var songId = $(button).prevAll(".songId").val();
	$.post("includes/handlers/ajax/removeFromPlaylist.php", { playlistId: playlistId, songId: songId })
		.done(function(error) {

			if(error != "") {
				alert(error);
				return;
			}

			//do something when ajax returns
			openPage("playlist.php?id=" + playlistId);
		});

}

function createPlaylist() {
	var alert = prompt("Please enter the name of your playlist");

	if(alert != null) {

		// $.post("includes/handlers/ajax/createPlaylist.php", { name: alert, username: userLoggedIn })
		// .done(function() {
		// 	//do something when ajax returns
		// 	openPage("yourMusic.php");
		// });

		let xhr4 = new XMLHttpRequest();
		xhr4.open('POST', 'includes/handlers/ajax/createPlaylist.php', true);
		xhr4.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
		xhr4.onload = function(){
			if(this.status === 200){
				console.log(this.responseText);
				openPage("yourMusic.php");
			}
		}

		xhr4.send(`name=${alert}&username=${userLoggedIn}`);

	}

}

function deletePlaylist(playlistId) {
	var prompt = confirm("Are you sure you want to delte this playlist?");

	if(prompt == true) {

		$.post("includes/handlers/ajax/deletePlaylist.php", { playlistId: playlistId })
		.done(function(error) {

			if(error != "") {
				alert(error);
				return;
			}

			//do something when ajax returns
			openPage("yourMusic.php");
		});


	}
}

function openPage(url) {

	if(url.indexOf("?") == -1) {
		url = url + "?";
	}

	var encodedUrl = encodeURI(url + "&userLoggedIn=" + userLoggedIn);
	$("#mainContent").load(encodedUrl);
	$("body").scrollTop(0);
	history.pushState(null,null, url);

}

function hideOptionsMenu() {
	var menu = $(".optionsMenu");
	if(menu.css("display") != "none") {
		menu.css("display", "none");
	}
}

function showOptionsMenu(button) {

	var songId = $(button).prevAll(".songId").val();
	var menu = $(".optionsMenu");
	var menuWidth = menu.width();
	menu.find(".songId").val(songId);

	var scrollTop = $(window).scrollTop(); //Distance from top of window to top of document
	var elementOffset = $(button).offset().top; //Distance from top of document

	var top = elementOffset - scrollTop;
	var left = $(button).position().left;

	menu.css({ "top": top + "px", "left": left - menuWidth + "px", "display": "inline" });

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