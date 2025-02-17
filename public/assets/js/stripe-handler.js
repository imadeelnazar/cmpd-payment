document.addEventListener("DOMContentLoaded", function () {
  const form = document.getElementById("school_activation_form");

  if (!form) return;

  form.addEventListener("submit", async function (event) {
    event.preventDefault();

    // Validate the selected payment method
    const paymentMethodElement = document.querySelector(
      'input[name="form_fields[payment_method]"]:checked'
    );
    if (!paymentMethodElement) {
      alert("Please select a payment method.");
      return;
    }
    const paymentMethod = paymentMethodElement.value;

    // Get form field values
    const formData = {
      amount: cmpd_ajax.stripe_amount,
      description: cmpd_ajax.stripe_description,
      email: form.querySelector('input[name="form_fields[youremail]"]').value.trim(),
      schoolname: form.querySelector('input[name="form_fields[schoolname]"]').value.trim(),
      schoolId: form.querySelector('input[name="form_fields[schoolId]"]').value.trim(),
      phone: form.querySelector('input[name="form_fields[yourphone]"]').value.trim(),
      name: form.querySelector('input[name="form_fields[yourname]"]').value.trim(),
      acceptance: form.querySelector('input[name="form_fields[acceptance]"]:checked')?.value || "",
      ifDuplicates: form.querySelector('textarea[name="form_fields[if_duplicates]"]').value.trim(),
      nonce: cmpd_ajax.nonce, // Add nonce for CSRF protection
    };

    // Basic validation for required fields
    if (!formData.email || !formData.name) {
      alert("Please provide your name and email.");
      return;
    }

    // Ensure amount and description are valid
    if (!formData.amount || !formData.description) {
      alert("Invalid payment details. Please try again.");
      return;
    }

    // Sanitize and validate email format
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formData.email)) {
      alert("Invalid email address.");
      return;
    }

    // Prepare URL parameters
    const urlParams = new URLSearchParams({
      method: paymentMethod,
      amount: formData.amount,
      email: formData.email,
      schoolId: formData.schoolId,
      schoolname: formData.schoolname,
      phone: formData.phone,
      name: encodeURIComponent(formData.name),
      description: encodeURIComponent(formData.description),
      acceptance: formData.acceptance,
      ifDuplicates: encodeURIComponent(formData.ifDuplicates),
      _wpnonce: formData.nonce, // Pass the nonce for server-side verification
    });

    // Redirect based on payment method
    try {
      if (paymentMethod === "paypal") {
        window.location.href = `/classmateplaydate/process-paypal-payment?${urlParams.toString()}`;
      } else if (paymentMethod === "stripe") {
        window.location.href = `/classmateplaydate/process-stripe-payment?${urlParams.toString()}`;
      } else {
        alert("Invalid payment method selected.");
      }
    } catch (error) {
      console.error("Error redirecting to payment:", error);
      alert("An error occurred while processing your payment. Please try again.");
    }
  });
});
