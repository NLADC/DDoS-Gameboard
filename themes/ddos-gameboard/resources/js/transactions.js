export class TransactionNotificationController {

	static fields = {
		action: {
			name: 			'info|string',
			description: 	'info|string',
			tag: 			'info|string',
			start: 			'info|datetime',
			length: 		'info|number',
			delay: 			'info|number|trigger0',
			extension: 		'info|number|trigger0',
			hasIssues:      'warning|boolean',
			isCancelled: 	'warning|boolean'
		}
	}

	static process(type, transaction, context) {
		// Find which fields we can report on
		var notification = null;

		if (type == 'action') {
			notification = this.createNotification(type, transaction, context.action);

			if(notification) {
				notification.title = context.name;
				this.renderNotification(notification);
			}

		} else if (type == 'showNotify') {

		    notification = context;
            this.renderNotification(notification);

        } else if(type == 'system') {

            var command = transaction.command;

            if (command == 'Countdown') {

                /*
                setInterval(function() {
                    location.reload();
                }.bind(this), 2500);
                */

                notification = {
                    type: transaction.type,
                    title: 'Countdown',
                    message: [transaction.message]
                };
                this.renderNotification(notification);

            } else if (command == 'sentNotify') {

                notification = {
                    type: transaction.type,
                    title: 'Notify',
                    message: [transaction.message]
                };
                this.renderNotification(notification);

            }
        }

		return notification;
	}

	static createNotification(type, transaction, data) {
		var notification = {
			type: [],
			title: '',
			message: []
		};

		Object.entries(this.fields[type]).forEach(function(fieldObj, idx) {
			var field = fieldObj[0];

			if(field in transaction && field in data) {
				var fieldSettings = fieldObj[1];

				var message = this.findMessage(field, fieldSettings, transaction[field], data[field]);
				var type = this.findType(fieldSettings);

				notification.type.push(type);
				if(message)
					notification.message.push(message);
			}
		}.bind(this));

		var highestUrgency = 'info';
		for(var i = 0; i < notification.type.length; i++) {
			var type = notification.type[i];

			if(type == 'error') {
				highestUrgency = 'error';
				break;
			} else if(type == 'warning')
				highestUrgency = 'warning';
			else if(type == 'success' && type != 'warning')
				highestUrgency = 'success';
		}
		notification.type = highestUrgency;

		if(notification.message.length)
			return notification;
	}

	static findMessage(field, fieldSettings, tValue, dValue) {
		if(fieldSettings.includes('number')) {
			if(tValue == 0 && fieldSettings.includes('trigger0'))
				return field + ' has been removed.';
			else if(tValue < dValue)
				return field + ' has been decreased.';
			else if(tValue > dValue)
				return field + ' has been increased.';
		}

		else if(fieldSettings.includes('datetime')) {
			if(tValue < dValue)
				return 'Has been rescheduled to an earlier time.';
			else if(tValue > dValue)
				return 'Has been rescheduled to a later time.';
		}

		else if(fieldSettings.includes('string')) {
			return field + ' has been updated.';
		}

		else if(fieldSettings.includes('boolean')) {
			if(field == 'isCancelled') {
				if(tValue)
					return 'Has been cancelled.';
				else
					return 'Is no longer cancelled.';
			} else if(field == 'hasIssues') {
				if(tValue)
					return 'Is having some issues.';
				else
					return 'No longer has issues';
			}
		}
	}

	static findType(fieldSettings) {
		if(fieldSettings.includes('error'))
			return 'error';
		else if(fieldSettings.includes('warning'))
			return 'warning';
		else if(fieldSettings.includes('success'))
			return 'success';
		else
			return 'info';
	}

	static renderNotification(notification) {
		if (notification.title.includes('Gifcountdown'))
			//notification.message ='<img src="/img/count-down-movie-countdown.gif">';
            notification.message ='';
		else {
			var renderedMsg = '';
			notification.message.forEach(function(message) {
				renderedMsg += '<p>' + message.replace(/^\w/, c => c.toUpperCase()) + '</p>';
			});
			notification.message = renderedMsg;
		}
	}
}
