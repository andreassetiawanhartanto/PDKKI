var helloApp = angular.module("helloApp", []);
	helloApp.controller("MainCtrl", function($scope) {
		$scope.name = "Calvin Hobbes";
		$scope.selected = "home";

		 $scope.select= function(item) {
        	$scope.selected = item;
		 };

		 $scope.isActive = function(item) {
		    return $scope.selected === item;
		 };	

	});