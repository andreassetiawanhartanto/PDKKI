var helloApp = angular.module("helloApp", []);
	helloApp.controller("MainCtrl",["$scope","$sce",function($scope,$sce) {
		$scope.name = "Calvin Hobbes";
		$scope.selected = "home";

		 $scope.select= function(item) {
        	$scope.selected = item;
		 };

		 $scope.isActive = function(item) {
		    return $scope.selected === item;
		 };

		 $scope.currentUrl = $sce.trustAsResourceUrl("https://www.youtube.com/embed/9np5y6ukrbc?autoplay=1");

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
		 }];

		 $scope.changeVideoSrc = function(video){
			 $scope.currentUrl = $sce.trustAsResourceUrl(video.url);
		 }

	}]);
