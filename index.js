var helloApp = angular.module("helloApp", ['ui.bootstrap']);
	helloApp.controller("MainCtrl",["$scope","$sce","$http",function($scope,$sce,$http) {
		$scope.name = "Calvin Hobbes";
		$scope.selected = "home";

		 $scope.select= function(item) {
        	$scope.selected = item;
		 };

		 $scope.isActive = function(item) {
		    return $scope.selected === item;
		 };

		 $scope.currentUrl = $sce.trustAsResourceUrl("https://www.youtube.com/embed/9np5y6ukrbc");

		 $scope.videos = [{
			 	"name" : "Bread From Heaven 2013 PDKKI Annual Retreat",
				"url" : "https://www.youtube.com/embed/d2XVyweHj4s?autoplay=1",
				"thumbnail" : "http://img.youtube.com/vi/d2XVyweHj4s/1.jpg",
		 },{
			 	"name" : "Retreat A New Beginning in Him Sneak Peek HD",
				"url" : "https://www.youtube.com/embed/9np5y6ukrbc?autoplay=1",
				"thumbnail" : "http://img.youtube.com/vi/9np5y6ukrbc/3.jpg",
		 },{
			 	"name" : "PDKKI Praise Rally 2014 Infinity",
				"url" : "https://www.youtube.com/embed/EriqJio5odQ?autoplay=1",
				"thumbnail" : "http://img.youtube.com/vi/EriqJio5odQ/2.jpg",
		 },{
			 	"name" : "Make me a Channel of your Peace - Angklung",
				"url" : "https://www.youtube.com/embed/g5yZetwkKaM?autoplay=1",
				"thumbnail" : "http://img.youtube.com/vi/g5yZetwkKaM/1.jpg",
		 },{
			 	"name" : "Malam Penyegaran Rohani 2015: The Winner is Love - Sneak Peek #2",
				"url" : "https://www.youtube.com/embed/zWMI3rdR1oU?autoplay=1",
				"thumbnail" : "http://img.youtube.com/vi/zWMI3rdR1oU/1.jpg",
		 },{
			 	"name" : "Bread From Heaven 2013 PDKKI Annual Retreat",
				"url" : "https://www.youtube.com/embed/PnNwnSpT7-A?autoplay=1",
				"thumbnail" : "http://img.youtube.com/vi/PnNwnSpT7-A/1.jpg",
		 },{
			 	"name" : "PDKKI MELBOURNE CHRISTMAS 2014",
				"url" : "https://www.youtube.com/embed/Ov7L4qiq4lM?autoplay=1",
				"thumbnail" : "http://img.youtube.com/vi/Ov7L4qiq4lM/1.jpg",
		 },{
			 	"name" : "Retreat KADO 9-11 Oct 2015",
				"url" : "https://www.youtube.com/embed/VgsSjRPJoCM?autoplay=1",
				"thumbnail" : "http://img.youtube.com/vi/VgsSjRPJoCM/2.jpg",
		 },{
			 	"name" : "Retret K.A.D.O 2015",
				"url" : "https://www.youtube.com/embed/URKEhHCRoxk?autoplay=1",
				"thumbnail" : "http://img.youtube.com/vi/URKEhHCRoxk/2.jpg",
		 },{
			 	"name" : "PDKKI Melbourne: A Snapshot",
				"url" : "https://www.youtube.com/embed/HYlPmc3WiPc?autoplay=1",
				"thumbnail" : "http://img.youtube.com/vi/HYlPmc3WiPc/2.jpg",
		 },{
			 	"name" : "Retreat 2012 Teaser",
				"url" : "https://www.youtube.com/embed/hc4PPI1g3pQ?autoplay=1",
				"thumbnail" : "http://img.youtube.com/vi/hc4PPI1g3pQ/2.jpg",
		 },{
			 	"name" : "PDKKI Melbourne Choir",
				"url" : "https://www.youtube.com/embed/ioDJj4KbBi0?autoplay=1",
				"thumbnail" : "http://img.youtube.com/vi/ioDJj4KbBi0/2.jpg",
		 }];

		 $scope.changeVideoSrc = function(video){
			 $scope.currentUrl = $sce.trustAsResourceUrl(video.url);
		 }

		 $scope.photos = [
		 {
		 	"url" : "./img/slider/2.jpg"
		 },{
		 	"url" : "./img/slider/3.jpg"
		 },{
		 	"url" : "./img/slider/4.jpg"
		 },{
		 	"url" : "./img/slider/5.jpg"
		 },{
		 	"url" : "./img/slider/6.jpg"
		 },{
		 	"url" : "./img/slider/7.jpg"
		 },{
		 	"url" : "./img/slider/8.jpg"
		 },{
		 	"url" : "./img/slider/9.jpg"
		 },{
		 	"url" : "./img/slider/10.jpg"
		 },{
		 	"url" : "./img/slider/11.jpg"
		 },{
		 	"url" : "./img/slider/12.jpg"
		 },{
		 	"url" : "./img/slider/13.jpg"
		 },{
		 	"url" : "./img/slider/14.jpg"
		 },{
		 	"url" : "./img/slider/18.jpg"
		 },{
		 	"url" : "./img/slider/16.jpg"
		 },{
		 	"url" : "./img/slider/19.jpg"
		 },{
		 	"url" : "./img/slider/20.jpg"
		 },{
		 	"url" : "./img/slider/21.jpg"
		 },{
		 	"url" : "./img/slider/22.jpg"
		 },{
		 	"url" : "./img/slider/23.jpg"
		 }
		 ];

		//  setTimeout(function(){
		// 			 FB.api(
		// 				 '/me/',
		// 				 'GET',
		// 				 {"fields":"albums.limit(5){photos.limit(10)}","access_token":"CAACEdEose0cBAOeXMT2bkoNiRomWolEktR6581KXgD04JY65sK4QJII3hcjHbiRhTuxrMsZAHd2AM1C7knZC3uSaCVsZBYLA9N4W9GJXyRsjMTBH44utOZC7Xh0lOhr8PD8oe0ir3yrNdxUFbZCst5NFVdZC7y9t2bGsTO7mwNW7mjcgVpEMcJz3lYbBPAXxVGuCTHvAAKiAZDZD"},
		// 				 function(response) {
		// 					 	 $scope.albums = angular.copy(response.albums.data);
		// 						 console.log($scope.albums);
		// 						 $scope.$apply();
		// 				 }
		// 		 );
		// 	}, 3000);
	}]);

	// https://developers.facebook.com/tools/explorer/
