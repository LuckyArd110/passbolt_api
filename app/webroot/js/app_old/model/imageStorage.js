steal(
	'mad/model'
).then(function () {

	/*
	 * @class passbolt.model.ImageStorage
	 * @inherits {mad.model.Model}
	 * @parent index
	 * 
	 * The Image Storage model
	 * 
	 * @constructor
	 * Creates a category
	 * @param {array} options
	 * @return {passbolt.model.ImageStorage}
	 */
	mad.model.Model('passbolt.model.ImageStorage', /** @static */	{

	}, /** @prototype */ {

		/**
		 * Get the image path
		 * @param {passbolt.model.ImageStorage} img The target image
		 * @param {string} version (optional) The version to get
		 * @return {string} The image path
		 */
		'imagePath': function(version) {
			if (typeof this.url == 'undefined') {
				return '';
			}
			if (typeof this.url[version] == 'undefined') {
				return '';
			}
			else {
				return this.url[version];
			}
		}

	});
});