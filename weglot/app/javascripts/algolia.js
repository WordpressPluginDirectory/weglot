document.addEventListener('DOMContentLoaded', function () {
	let debounceTimers = new Map();

	xhook.before(function (request, sendRequest) {
		if (request.url && request.url.includes('x-algolia-agent') && weglotData.original_language !== weglotData.current_language) {

			let parsedBody;
			try {
				parsedBody = JSON.parse(request.body);
			} catch (error) {
				console.error('Failed to parse request.body:', error);
				sendRequest();
				return;
			}

			let queryValue;
			if (parsedBody.requests && parsedBody.requests.length > 0) {
				queryValue = parsedBody.requests[0].params.match(/(?:^|&)query=([^&]*)/)[1];
			} else if (parsedBody.query) {
				queryValue = parsedBody.query;
			} else {
				console.error('Unexpected parsedBody structure:', parsedBody);
				sendRequest();
				return;
			}

			// Extract a unique identifier for the request type (e.g., index name)
			let requestType = request.url.match(/indexes\/([^\/?]+)/)[1];

			if (debounceTimers.has(requestType)) {
				clearTimeout(debounceTimers.get(requestType));
			}

			debounceTimers.set(requestType, setTimeout(() => {
				reverseTranslate(weglotData.api_key, weglotData.current_language, weglotData.original_language, 'https://' + window.location.hostname, queryValue, 1)
					.then(data => {
						if (data.to_words[0] !== undefined) {
							const reverseWord = data.to_words[0];
							if (parsedBody.requests && parsedBody.requests.length > 0) {
								parsedBody.requests[0].params = parsedBody.requests[0].params.replace(/query=[^&]*/, 'query=' + reverseWord);
							} else if (parsedBody.query) {
								parsedBody.query = reverseWord;
							}
							request.body = JSON.stringify(parsedBody);
							let url = request.url;
							let apiKey = weglotData.api_key.replace('wg_', '');
							url = url.replace(/^https?:\/\//, '');
							request.url = 'https://proxy.weglot.com/' + apiKey + '/' + weglotData.original_language + '/' + weglotData.current_language + '/' + url;
							sendRequest();
						}
					});
				debounceTimers.delete(requestType); // Clean up the timer
			}, 300)); // Adjust the debounce delay as needed (e.g., 300ms)

		} else {
			sendRequest();
		}
	});

	xhook.after(function (request, response) {
		if (request.url && request.url.includes('x-algolia-agent') && weglotData.original_language !== weglotData.current_language) {
			let apiKey = weglotData.api_key.replace('wg_', '');
			let url = request.url;
			url = url.replace(/^https?:\/\//, '');
			const proxifyUrl = 'https://proxy.weglot.com/' + apiKey + '/' + weglotData.original_language + '/' + weglotData.current_language + '/' + url;
		}
	});
});

function reverseTranslate(apiKey, l_from, l_to, request_url, word, t) {
	const requestBody = {
		"l_from": l_from,
		"l_to": l_to,
		"request_url": request_url,
		"words": [
			{"w": word, "t": t}
		]
	};

	const apiUrl = `https://api.weglot.com/translate?api_key=${apiKey}`;

	return fetch(apiUrl, {
		method: 'POST',
		headers: {
			'Content-Type': 'application/json'
		},
		body: JSON.stringify(requestBody)
	})
		.then(response => {
			if (!response.ok) {
				throw new Error('Network response was not ok');
			}
			return response.json();
		})
		.catch(error => {
			console.error('There was a problem with your fetch operation:', error);
		});
}
