// console.log("Connected");

const hideLogin = document.querySelector("#hideLogin");
const hideSignup = document.querySelector("#hideSignup");
const loginForm = document.querySelector("#loginForm");
const registerForm = document.querySelector("#RegisterForm");


hideLogin.addEventListener('click', function(){
	loginForm.style.display = 'none';
	registerForm.style.display = 'block';

});

hideSignup.addEventListener('click', function(){
	registerForm.style.display = 'none';
	loginForm.style.display = 'block';

});