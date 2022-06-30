function startLoader() {
  $("div.spanner").addClass("show");
  $("div.overlay").addClass("show");
}

function endLoader() {
  $("div.spanner").removeClass("show");
  $("div.overlay").removeClass("show");
}

// baseURL: `/gst/backend/`,
const axiosInstance = axios.create({
  baseURL: `/backend/`,
  credentials: "include",
  withCredentials: true,
});
// render file
var loadFile = function (event, preview_id) {
  var output = document.getElementById(preview_id);
  output.src = URL.createObjectURL(event.target.files[0]);
  logoFileData = event.target?.files[0];
  console.log(logoFileData);
  output.onload = function () {
    URL.revokeObjectURL(output.src); // free memory
  };
};
