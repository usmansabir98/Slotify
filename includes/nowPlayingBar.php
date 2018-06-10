<?php 
	$songQuery = mysqli_query($con, "SELECT id FROM songs ORDER BY rand() LIMIT 10");
	$resultArray = array();
	while($row = mysqli_fetch_array($songQuery)){
		array_push($resultArray, $row['id']);
	}

	$jsonArray = json_encode($resultArray);
 ?>



<div id="nowPlayingBarContainer">
	<div id="nowPlayingBar">
		<div id="nowPlayingLeft">
			<div class="content">
				<span class="albumLink">
					<img class="albumArtwork" src="https://lh3.googleusercontent.com/VT-PqxMMsA2wPy7kzmuKGDIzaA3AGuXKExqnfOfwTEy5AvLIMTranbfNGheRr457RD4=s180">
				</span>

				<div class="trackInfo">

					<span class="trackName">
						<span>Happy Birthday</span>
					</span>

					<span class="artistName">
						<span>Reece Kenney</span>
					</span>

				</div>
			</div>
		</div>

		<div id="nowPlayingCenter">
			<div class="content playerControls">
				
				<div class="buttons">

					<button class="controlButton shuffle" title="Shuffle button">
						<img src="assets/images/icons/shuffle.png" alt="Shuffle">
					</button>

					<button class="controlButton previous" title="Previous button">
						<img src="assets/images/icons/previous.png" alt="Previous">
					</button>

					<button class="controlButton play" title="Play button">
						<img src="assets/images/icons/play.png" alt="Play">
					</button>

					<button class="controlButton pause" title="Pause button" style="display: none;">
						<img src="assets/images/icons/pause.png" alt="Pause">
					</button>

					<button class="controlButton next" title="Next button">
						<img src="assets/images/icons/next.png" alt="Next">
					</button>

					<button class="controlButton repeat" title="Repeat button">
						<img src="assets/images/icons/repeat.png" alt="Repeat">
					</button>

				</div>


				<div class="playbackBar">

					<span class="progressTime current">0.00</span>

					<div class="progressBar">
						<div class="progressBarBg">
							<div class="progress"></div>
						</div>
					</div>

					<span class="progressTime remaining">0.00</span>


				</div>

			</div>
		</div>

		<div id="nowPlayingRight">
			<div class="volumeBar">

				<button class="controlButton volume" title="Volume button">
					<img src="assets/images/icons/volume.png" alt="Volume">
				</button>

				<div class="progressBar">
					<div class="progressBarBg">
						<div class="progress"></div>
					</div>
				</div>

			</div>
		</div>
	</div>
</div>

<script>
	document.addEventListener('DOMContentLoaded', function(){
		let newPlaylist = <?php echo $jsonArray; ?>;
		audioElement = new Audio();

		setTrack(newPlaylist[0], newPlaylist, false);
		updateVolumeProgressBar(audioElement.audio);

		document.querySelector('#nowPlayingBarContainer').addEventListener('mousedown', function(e){
				e.preventDefault();
			});
		document.querySelector('#nowPlayingBarContainer').addEventListener('mousemove', function(e){
				e.preventDefault();
			});
		document.querySelector('#nowPlayingBarContainer').addEventListener('touchstart', function(e){
				e.preventDefault();
			});
		document.querySelector('#nowPlayingBarContainer').addEventListener('touchmove', function(e){
				e.preventDefault();
			});

		document.querySelector(".playbackBar .progressBar").addEventListener('mousedown', function() {
			mouseDown = true;
		});

		document.querySelector(".playbackBar .progressBar").addEventListener('mousemove', function(e) {
			if(mouseDown == true) {
				//Set time of song, depending on position of mouse
				timeFromOffset(e, this);
			}
		});

		document.querySelector(".playbackBar .progressBar").addEventListener('mouseup', function(e) {
			timeFromOffset(e, this);
		});

		document.querySelector(".volumeBar .progressBar").addEventListener('mousedown', function() {
			mouseDown = true;
		});

		document.querySelector(".volumeBar .progressBar").addEventListener('mousemove', function(e) {
			if(mouseDown == true) {
				const percentage = e.offsetX / this.offsetWidth

				if(percentage>=0 && percentage<=1){
					audioElement.audio.volume = percentage;
				}
			}
		});

		document.querySelector(".volumeBar .progressBar").addEventListener('mouseup', function(e) {
			const percentage = e.offsetX / this.offsetWidth

			if(percentage>=0 && percentage<=1){
				audioElement.audio.volume = percentage;
			};
		});

		document.addEventListener('mouseup', function() {
			mouseDown = false;
		});
	});

	function timeFromOffset(mouse, progressBar) {
		const percentage = mouse.offsetX / progressBar.offsetWidth * 100;
		const seconds = audioElement.audio.duration * (percentage / 100);
		audioElement.setTime(seconds);
	}

	function setTrack(trackId, newPlaylist, play){
		if(newPlaylist != currentPlaylist) {
			currentPlaylist = newPlaylist;
			shufflePlaylist = currentPlaylist.slice();
			shuffleArray(shufflePlaylist);
		}

		if(shuffle){
			currentIndex = shufflePlaylist.indexOf(trackId);
		}
		else{
			currentIndex = currentPlaylist.indexOf(trackId);
		}

		pauseSong();

		// first ajax call for getting song
		let xhr = new XMLHttpRequest();
		xhr.open('POST', 'includes/handlers/ajax/getSongJson.php', true);
		xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
		xhr.onload = function(){
			if(this.status === 200){
				// console.log(this.responseText);
				const track = JSON.parse(this.responseText);

				document.querySelector('.trackName span').textContent = track.title;

				// another ajax call for getting artist
				let xhr2 = new XMLHttpRequest();
				xhr2.open('POST', 'includes/handlers/ajax/getArtistJson.php', true);
				xhr2.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
				xhr2.onload = function(){
					if(this.status === 200){
						const artist = JSON.parse(this.responseText);
						document.querySelector('.artistName span').textContent = artist.name;

					}
				}

				xhr2.send(`artistId=${track.artist}`);

				let xhr3 = new XMLHttpRequest();
				xhr3.open('POST', 'includes/handlers/ajax/getAlbumJson.php', true);
				xhr3.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
				xhr3.onload = function(){
					if(this.status === 200){
						const album = JSON.parse(this.responseText);
						document.querySelector('.albumLink img').src = album.artworkPath;

					}
				}

				xhr3.send(`albumId=${track.album}`);

				audioElement.setTrack(track);
				if(play){
					playSong();
				}

			}
		}

		xhr.send(`songId=${trackId}`);

		
	}

	const playBtn = document.querySelector('.controlButton.play');
	const pauseBtn = document.querySelector('.controlButton.pause');
	const nextBtn = document.querySelector('.controlButton.next');
	const prevBtn = document.querySelector('.controlButton.previous');
	const repeatBtn = document.querySelector('.controlButton.repeat');
	const volumeBtn = document.querySelector('.controlButton.volume');
	const shuffleBtn = document.querySelector('.controlButton.shuffle');

	playBtn.addEventListener('click', playSong);
	pauseBtn.addEventListener('click', pauseSong);
	nextBtn.addEventListener('click', nextSong);
	prevBtn.addEventListener('click', prevSong);
	repeatBtn.addEventListener('click', setRepeat);
	volumeBtn.addEventListener('click', setMute);
	shuffleBtn.addEventListener('click', setShuffle);

	function playSong(){
		// console.log('song played');
		if(audioElement.audio.currentTime == 0){
			let xhr2 = new XMLHttpRequest();
			xhr2.open('POST', 'includes/handlers/ajax/updatePlays.php', true);
			xhr2.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
			xhr2.send(`songId=${audioElement.currentlyPlaying.id}`);
		}

		playBtn.style.display = 'none';
		pauseBtn.style.display = 'inline-block';
		audioElement.play();
	}

	function pauseSong(){
		// console.log('paused');
		playBtn.style.display = 'inline-block';
		pauseBtn.style.display = 'none';
		audioElement.pause();
	}

	function prevSong(){
		if(currentIndex==0 || audioElement.audio.currentTime>=3){
			audioElement.setTime(0);
		}
		else{
			currentIndex--;
			setTrack(currentPlaylist[currentIndex], currentPlaylist);
		}
	}

	function nextSong(){

		if(repeat){
			audioElement.setTime(0);
			playSong();
			return;
		}

		if(currentIndex === currentPlaylist.length-1){
			currentIndex = 0;
		}
		else{
			currentIndex++;
		}
		const trackToPlay = shuffle?shufflePlaylist[currentIndex]:currentPlaylist[currentIndex];
		setTrack(trackToPlay, currentPlaylist, true);
	}

	function setRepeat(){
		repeat = !repeat;
		imgName = repeat?'repeat-active.png':'repeat.png';

		document.querySelector('.controlButton.repeat img').src = `assets/images/icons/${imgName}`;
	}

	function setShuffle(){
		shuffle = !shuffle;
		imgName = shuffle?'shuffle-active.png':'shuffle.png';

		document.querySelector('.controlButton.shuffle img').src = `assets/images/icons/${imgName}`;

		if(shuffle == true) {
			//Randomize playlist
			shuffleArray(shufflePlaylist);
			currentIndex = shufflePlaylist.indexOf(audioElement.currentlyPlaying.id);
		}
		else {
			//shuffle has been deactivated
			//go back to regular playlist
			currentIndex = currentPlaylist.indexOf(audioElement.currentlyPlaying.id);
		}
	}

	function shuffleArray(a) {
    var j, x, i;
    for (i = a.length; i; i--) {
        j = Math.floor(Math.random() * i);
        x = a[i - 1];
        a[i - 1] = a[j];
        a[j] = x;
    }
	}

	function setMute(){
		audioElement.audio.muted = !audioElement.audio.muted;
		imgName = audioElement.audio.muted?'volume-mute.png':'volume.png';

		document.querySelector('.controlButton.volume img').src = `assets/images/icons/${imgName}`;
	}

</script>