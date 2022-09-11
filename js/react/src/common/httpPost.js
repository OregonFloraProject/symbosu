/**
 * @param url URL to POST
 * @returns {Promise<string>} Either the response text or error code/text
 */
function httpPost(url) {
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
    req.send();
  });
}

export default httpPost;