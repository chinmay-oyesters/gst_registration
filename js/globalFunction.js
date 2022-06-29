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
  baseURL: `/gst/backend/`,
  credentials: "include",
  withCredentials: true,
});