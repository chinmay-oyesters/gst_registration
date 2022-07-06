function startLoader() {
  $("div.spanner").addClass("show");
  $("div.overlay").addClass("show");
}

function endLoader() {
  $("div.spanner").removeClass("show");
  $("div.overlay").removeClass("show");
}
function parseJwt(token) {
  var base64Url = token.split(".")[1];
  var base64 = base64Url.replace(/-/g, "+").replace(/_/g, "/");
  var jsonPayload = decodeURIComponent(
    atob(base64)
      .split("")
      .map(function (c) {
        return "%" + ("00" + c.charCodeAt(0).toString(16)).slice(-2);
      })
      .join("")
  );
  return JSON.parse(jsonPayload);
}
function getCookie(name) {
  const value = `; ${document.cookie}`;
  const parts = value.split(`; ${name}=`);
  let cookieNow = "";
  let cookieData = null;
  if (parts.length === 2) cookieNow = parts.pop().split(";").shift();
  if (cookieNow !== "") {
    cookieData = parseJwt(cookieNow);
    return cookieData;
  }
  return cookieData;
}
function authenticate() {
  let isLoggedIn = localStorage.getItem("isLoggedIn");
  if (isLoggedIn === "false" || isLoggedIn === null) {
    location.href = "index.html";
  }
  let cookieData = getCookie("user_jwt");
  if (!cookieData?.user_email) {
    localStorage.setItem("isLoggedIn", "false");
    location.href = "index.html";
  }
}
function authenticateAdmin() {
  let isLoggedIn = localStorage.getItem("isAdminLoggedIn");
  if (isLoggedIn === "false" || isLoggedIn === null) {
    location.href = "index.html";
  }
  let cookieData = getCookie("admin_jwt");
  if (!cookieData?.admin_email) {
    localStorage.setItem("isAdminLoggedIn", "false");
    location.href = "index.html";
  }
}
// baseURL: `/gst/backend/`,
const axiosInstance = axios.create({
  baseURL: `/gst/backend/`,
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
const validateEmail = (email) => {
  var re = /\S+@\S+\.\S+/;
  return re.test(email);
};
function validatePAN(panVal) {
  var regpan = /^([a-zA-Z]){5}([0-9]){4}([a-zA-Z]){1}?$/;
  if (regpan.test(panVal)) {
    return true;
  } else {
    return false;
  }
}
function isAlphaNumeric(str) {
  var code, i, len;
  var isNumeric = false;
  var isChars = false;
  for (i = 0, len = str.length; i < len; i++) {
    code = str.charCodeAt(i);
    if (
      !(code > 47 && code < 58) && // numeric (0-9)
      !(code > 64 && code < 91) && // upper alpha (A-Z)
      !(code > 96 && code < 123)
    ) {
      // lower alpha (a-z)
      return false;
    } else {
      if (code > 47 && code < 58) {
        isNumeric = true;
      }
      if ((code > 64 && code < 91) || (code > 96 && code < 123)) {
        isChars = true;
      }
    }
  }
  if (isNumeric && isChars) return true;
  else return false;
}
function getColorCode(index) {
  let colorArray = ["#6993FF", "#FFAA07", "#8D56FC", "#F64E60"];
  let indexReturn = index % 4;
  return colorArray[indexReturn];
}

function adminSignOut() {
  startLoader();
  let cookieData = getCookie("admin_jwt");
  if (cookieData?.admin_email) {
    axiosInstance.post("admin_logout").then(
      (response) => {
        console.log(response);
        endLoader();
        if (response.status === 200) {
          // localStorage.setItem("isAdminLoggedIn", "false");
          // location.href = "index.html";
        } else {
          $.notify(
            {
              title: "",
              message: response.data?.msg,
              icon: "fa fa-times",
            },
            {
              type: "danger",
            }
          );
        }
      },
      (error) => {
        endLoader();
        console.log("error", error);
        $.notify(
          {
            title: "",
            message: `Something went wrong! Please try again.`,
            icon: "fa fa-times",
          },
          {
            type: "danger",
          }
        );
      }
    );
  } else {
    localStorage.setItem("isAdminLoggedIn", "false");
    location.href = "index.html";
  }
}

function arrayMove(arr, fromIndex, toIndex) {
  var element = arr[fromIndex];
  arr.splice(fromIndex, 1);
  arr.splice(toIndex, 0, element);
}

function getUrlVars() {
  var vars = {};
  var parts = window.location.href.replace(
    /[?&]+([^=&]+)=([^&]*)/gi,
    function (m, key, value) {
      vars[key] = value;
    }
  );
  return vars;
}

//
function validateAllFields(validateFieldList) {
  for (let i = 0; i < validateFieldList.length; i++) {
    const element = validateFieldList[i];
    if (element?.field_required) {
      switch (element?.field_type) {
        case "Text":
          let nowValue = document.getElementById(
            `${element?.field_type}_${element?.field_id}${
              element?.field_parent_id || ""
            }`
          ).value;
          if (nowValue == "" || nowValue == null || nowValue == undefined) {
            $.notify(
              {
                title: "",
                message: `Please enter ${element?.field_title.toLocaleLowerCase()}`,
                icon: "fa fa-times",
              },
              {
                type: "danger",
              }
            );
            return false;
          }
          switch (element?.field_validation) {
            case "Email":
              if (!validateEmail(nowValue)) {
                $.notify(
                  {
                    title: "",
                    message: `Please enter a valid ${element?.field_title.toLocaleLowerCase()}`,
                    icon: "fa fa-times",
                  },
                  {
                    type: "danger",
                  }
                );
                return false;
              }
              break;
            case "Mobile-Number":
              if (nowValue?.length != 10) {
                $.notify(
                  {
                    title: "",
                    message: `Please enter a valid ${element?.field_title.toLocaleLowerCase()}`,
                    icon: "fa fa-times",
                  },
                  {
                    type: "danger",
                  }
                );
                return false;
              }
              break;
            case "PAN":
              if (!validatePAN(nowValue)) {
                $.notify(
                  {
                    title: "",
                    message: `Please enter a valid ${element?.field_title.toLocaleLowerCase()}`,
                    icon: "fa fa-times",
                  },
                  {
                    type: "danger",
                  }
                );
                return false;
              }
              break;
            case "Alpha-Numeric":
              if (!isAlphaNumeric(nowValue)) {
                $.notify(
                  {
                    title: "",
                    message: `Please enter a alpha-numeric value`,
                    icon: "fa fa-times",
                  },
                  {
                    type: "danger",
                  }
                );
                return false;
              }
              break;
            default:
              break;
          }
          break;
        case "Single-Select":
          let radioButtons = document.querySelectorAll(
            `input[name='${element?.field_type}_${element?.field_id}${
              element?.field_parent_id || ""
            }']`
          );
          let selectedSize = null;
          for (let radioButton of radioButtons) {
            if (radioButton.checked) {
              selectedSize = radioButton.value;
              break;
            }
          }
          if (selectedSize == null) {
            $.notify(
              {
                title: "",
                message: `Please select ${element?.field_title.toLocaleLowerCase()}`,
                icon: "fa fa-times",
              },
              {
                type: "danger",
              }
            );
            return false;
          }
          break;
        case "Dropdown":
          let nowDropDown = document.getElementById(
            `${element?.field_type}_${element?.field_id}${
              element?.field_parent_id || ""
            }`
          ).value;
          if (nowDropDown == "") {
            $.notify(
              {
                title: "",
                message: `Please select ${element?.field_title.toLocaleLowerCase()}`,
                icon: "fa fa-times",
              },
              {
                type: "danger",
              }
            );
            return false;
          }
          break;
        case "Multi-Select":
          let nowMultiValue = $(
            `#${element?.field_type}_${element?.field_id}${
              element?.field_parent_id || ""
            }`
          ).val();

          if (nowMultiValue?.length == 0) {
            $.notify(
              {
                title: "",
                message: `Please select ${element?.field_title.toLocaleLowerCase()}`,
                icon: "fa fa-times",
              },
              {
                type: "danger",
              }
            );
            return false;
          }
          break;
        case "Date":
          let dateValue = document.getElementById(
            `${element?.field_type}_${element?.field_id}${
              element?.field_parent_id || ""
            }`
          ).value;
          if (dateValue == "") {
            $.notify(
              {
                title: "",
                message: `Please select ${element?.field_title.toLocaleLowerCase()}`,
                icon: "fa fa-times",
              },
              {
                type: "danger",
              }
            );
            return false;
          }
        default:
          break;
      }
    }
  }
  return true;
}
function setFields(container_id_n, nowFields) {
  let container_id = document.getElementById(container_id_n);
  container_id.innerHTML = "";
  for (let i = 0; i < nowFields.length; i++) {
    const element = nowFields[i];
    switch (element?.field_type) {
      case "Text":
        container_id.innerHTML =
          container_id.innerHTML +
          `
                  <div class="form-group" >
                  <label class="control-label">${element?.field_title}</label>${
            element?.field_required
              ? '<span class="text-danger" style="font-size:23px;" >*</span>'
              : ""
          }
                  <input class="form-control" id='${element?.field_type}_${
            element?.field_id
          }${
            element?.field_parent_id || ""
          }' type="text" placeholder='${element?.field_title?.toLocaleLowerCase()}'   oninput='${
            element?.field_validation == "Number" ||
            element?.field_validation == "Mobile-Number"
              ? 'this.value=this.value.replace(/[^0-9]/g,"");'
              : ""
          }'>
                  </div>
                  `;
        break;
      case "Toggle":
        container_id.innerHTML =
          container_id.innerHTML +
          `
          <div class="form-group" >
          <label class="control-label">${element?.field_title}</label>${
            element?.field_required
              ? '<span class="text-danger" style="font-size:23px;" >*</span>'
              : ""
          }
              <div class="d-flex justify-content-between align-items-center" style="max-width:100px;">
                  <p> ${element?.field_values[0][0]}</p>
               <div class="toggle lg ">
              <label>
                <input type="checkbox" onchange="updateFieldValueAssociateDropDown(${
                  element?.field_id
                }${element?.field_parent_id || ""},'Toggle','${
            element?.field_values[1]
          }')"  id='${element?.field_type}_${element?.field_id}${
            element?.field_parent_id || ""
          }'><span class="button-indecator" ></span>
              </label>   
             </div>
             <p> ${element?.field_values[1][0]}</p>
                  </div>
          </div>
          <div id="field_value_associate_${element?.field_id}${
            element?.field_parent_id || ""
          }" ></div>
          `;
        break;
      case "Dropdown":
        container_id.innerHTML =
          container_id.innerHTML +
          `
              <div class="form-group">
                   <label for='${element?.field_type}_${element?.field_id}${
            element?.field_parent_id || ""
          }'>${element?.field_title}</label>${
            element?.field_required
              ? '<span class="text-danger" style="font-size:23px;" >*</span>'
              : ""
          }
                  <select onchange="updateFieldValueAssociateDropDown(${
                    element?.field_id
                  }${
            element?.field_parent_id || ""
          },'Dropdown','dropdown')" class="form-control" id='${
            element?.field_type
          }_${element?.field_id}${element?.field_parent_id || ""}'>
                      <option value="" >-Select One - </option>
                      ${element?.field_values
                        ?.map(
                          (fieldValue) =>
                            `<option value='${fieldValue}'>${fieldValue[0]}</option>`
                        )
                        .join("")}
                  </select>
              </div>
              <div id="field_value_associate_${element?.field_id}${
            element?.field_parent_id || ""
          }" ></div>
              `;
        break;
      case "Multi-Select":
        container_id.innerHTML =
          container_id.innerHTML +
          `
              <div class="form-group">
                   <label for='${element?.field_type}_${element?.field_id}${
            element?.field_parent_id || ""
          }'>${element?.field_title}</label>${
            element?.field_required
              ? '<span class="text-danger" style="font-size:23px;" >*</span>'
              : ""
          }
                  <select class="form-control js-example-basic-multiple" name="states[]" multiple="multiple" id='${
                    element?.field_type
                  }_${element?.field_id}${element?.field_parent_id || ""}' 
                  onchange="updateFieldValueAssociateDropDown(${
                    element?.field_id
                  }${
            element?.field_parent_id || ""
          },'Multi-Select','Multi-Select')"
                  >
                      ${element?.field_values
                        ?.map(
                          (fieldValue) =>
                            `<option value='${fieldValue}'>${fieldValue[0]}</option>`
                        )
                        .join("")}
                  </select>
              </div>
              <div id="field_value_associate_${element?.field_id}${
            element?.field_parent_id || ""
          }" ></div>
              `;
        break;
      case "Date":
        container_id.innerHTML =
          container_id.innerHTML +
          `
                <div class="form-group" >
                <label class="control-label">${element?.field_title}</label>${
            element?.field_required
              ? '<span class="text-danger" style="font-size:23px;" >*</span>'
              : ""
          }
                <input class="form-control" id='${element?.field_type}_${
            element?.field_id
          }${element?.field_parent_id || ""}' type="date"  >
                </div>
                `;
        break;
      default:
        break;
    }
  }
  updateSelect2();
}
function updateSelect2() {
  $(document).ready(function () {
    $(".js-example-basic-multiple").select2();
  });
}
