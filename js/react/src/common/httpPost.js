/**
 * @param url URL to POST
 * @returns {Promise<string>} Either the response text or error code/text
 */
function httpPost(url,mapParamString) {
  return new Promise((resolve, reject) => {
    const req = new XMLHttpRequest();
    req.onload = () => {
      if (req.status === 200) {
        resolve(req.responseText);
      } else {
        reject(`${req.status.toString()} ${req.statusText}`);
      }
    };
    req.open("POST", url);
		req.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
    req.send(mapParamString);
  });
}

export default httpPost;