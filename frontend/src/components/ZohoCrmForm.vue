<script>
import { ref, onMounted } from "vue";

export default {
  name: "ZohoCRMForm",
  setup() {
    const form = ref({
      account_name: "",
      account_website: "",
      account_phone: "",
      deal_name: "",
      deal_stage: "",
    });

    const errors = ref({});
    const loading = ref(false);
    const successMessage = ref("");
    const errorMessage = ref("");
    const authorized = ref(false);

    onMounted(async () => {
      const res = await fetch("/api/crm/status", {
        headers: { Accept: "application/json" },
      });
      const data = await res.json();
      authorized.value = data.authorized;
      console.log(authorized.value);
    });

    const inputClass = (hasError) => [
      "w-full px-4 py-2.5 rounded-lg border text-sm text-slate-800 placeholder-slate-400",
      "focus:outline-none focus:ring-2 transition-all duration-150 bg-white",
      hasError
        ? "border-red-400 focus:border-red-500 focus:ring-red-100"
        : "border-slate-200 focus:border-blue-500 focus:ring-blue-100",
    ];

    const selectClass = (hasError) => [
      "w-full px-4 py-2.5 rounded-lg border text-sm appearance-none",
      "focus:outline-none focus:ring-2 transition-all duration-150 bg-white pr-9",
      hasError
        ? "border-red-400 focus:border-red-500 focus:ring-red-100 text-slate-800"
        : "border-slate-200 focus:border-blue-500 focus:ring-blue-100 text-slate-800",
    ];

    const validate = () => {
      const e = {};
      if (!form.value.account_name.trim())
        e.account_name = "Account name is required";
      if (!form.value.account_website.trim())
        e.account_website = "Website is required";
      if (!form.value.account_phone.trim())
        e.account_phone = "Phone is required";
      if (!form.value.deal_name.trim()) e.deal_name = "Deal name is required";
      if (!form.value.deal_stage) e.deal_stage = "Deal stage is required";
      return e;
    };

    const handleSubmit = async () => {
      successMessage.value = "";
      errorMessage.value = "";
      errors.value = validate();

      if (Object.keys(errors.value).length > 0) return;

      loading.value = true;

      try {
        const response = await fetch("/api/crm/submit", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            Accept: "application/json",
          },
          body: JSON.stringify(form.value),
        });

        const responseText = await response.text();
        let data;
        try {
          data = JSON.parse(responseText);
        } catch {
          throw new Error(
            `Server returned invalid response. Make sure backend is running on port 8000.`,
          );
        }

        if (!response.ok) {
          if (data.errors) {
            errors.value = data.errors;
          } else {
            errorMessage.value =
              data.message || data.error || "An error occurred";
          }
        } else {
          successMessage.value =
            data.message ||
            "Deal and Account successfully created in Zoho CRM!";
          form.value = {
            account_name: "",
            account_website: "",
            account_phone: "",
            deal_name: "",
            deal_stage: "",
          };
        }
      } catch (error) {
        errorMessage.value = error.message;
      } finally {
        loading.value = false;
      }
    };

    return {
      form,
      errors,
      loading,
      successMessage,
      errorMessage,
      handleSubmit,
      inputClass,
      selectClass,
      authorized,
    };
  },
};
</script>

<template>
  <div class="min-h-screen bg-gray-100 flex items-center justify-center p-4">
    <div class="w-full max-w-2xl">
      <div v-if="!authorized" class="text-center mb-6">
        <p class="text-slate-600 mb-4">
          Zoho CRM is not connected. Please authorize first.
        </p>
        <a
          href="http://localhost:8000/zoho/auth"
          class="px-6 py-3 bg-blue-600 text-white rounded-xl font-semibold hover:bg-blue-700"
        >
          Connect Zoho CRM
        </a>
      </div>

      <div v-else>
        <div class="text-center mb-8">
          <div
            class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-blue-600 shadow-lg mb-4"
          >
            <svg
              class="w-7 h-7 text-white"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
              />
            </svg>
          </div>
          <h1 class="text-2xl font-bold text-slate-800">
            Zoho CRM Integration
          </h1>
          <p class="text-slate-500 mt-1 text-sm">
            Create a new Deal and linked Account in one step
          </p>
        </div>

        <div
          class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden"
        >
          <form @submit.prevent="handleSubmit">
            <div class="px-6 pt-6 pb-4">
              <div class="flex items-center gap-3 mb-5">
                <span
                  class="flex items-center justify-center w-7 h-7 rounded-full bg-blue-600 text-white text-xs font-bold shrink-0"
                  >1</span
                >
                <h2 class="text-base font-semibold text-slate-700">
                  Account Details
                </h2>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="flex flex-col gap-1.5">
                  <label
                    class="text-xs font-semibold text-slate-600 uppercase tracking-wide"
                  >
                    Account Name <span class="text-red-500">*</span>
                  </label>
                  <input
                    v-model="form.account_name"
                    type="text"
                    placeholder="e.g. Acme Corp"
                    :class="inputClass(errors.account_name)"
                  />
                  <p
                    v-if="errors.account_name"
                    class="text-xs text-red-600 flex items-center gap-1"
                  >
                    <svg
                      class="w-3 h-3 shrink-0"
                      fill="currentColor"
                      viewBox="0 0 20 20"
                    >
                      <path
                        fill-rule="evenodd"
                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                        clip-rule="evenodd"
                      />
                    </svg>
                    {{ errors.account_name }}
                  </p>
                </div>

                <div class="flex flex-col gap-1.5">
                  <label
                    class="text-xs font-semibold text-slate-600 uppercase tracking-wide"
                  >
                    Website <span class="text-red-500">*</span>
                  </label>
                  <input
                    v-model="form.account_website"
                    type="url"
                    placeholder="https://example.com"
                    :class="inputClass(errors.account_website)"
                  />
                  <p
                    v-if="errors.account_website"
                    class="text-xs text-red-600 flex items-center gap-1"
                  >
                    <svg
                      class="w-3 h-3 shrink-0"
                      fill="currentColor"
                      viewBox="0 0 20 20"
                    >
                      <path
                        fill-rule="evenodd"
                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                        clip-rule="evenodd"
                      />
                    </svg>
                    {{ errors.account_website }}
                  </p>
                </div>

                <div class="flex flex-col gap-1.5 sm:col-span-2">
                  <label
                    class="text-xs font-semibold text-slate-600 uppercase tracking-wide"
                  >
                    Phone <span class="text-red-500">*</span>
                  </label>
                  <input
                    v-model="form.account_phone"
                    type="tel"
                    placeholder="+1 (555) 000-0000"
                    :class="inputClass(errors.account_phone)"
                  />
                  <p
                    v-if="errors.account_phone"
                    class="text-xs text-red-600 flex items-center gap-1"
                  >
                    <svg
                      class="w-3 h-3 shrink-0"
                      fill="currentColor"
                      viewBox="0 0 20 20"
                    >
                      <path
                        fill-rule="evenodd"
                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                        clip-rule="evenodd"
                      />
                    </svg>
                    {{ errors.account_phone }}
                  </p>
                </div>
              </div>
            </div>

            <div class="mx-6 border-t border-slate-100"></div>

            <div class="px-6 pt-5 pb-6">
              <div class="flex items-center gap-3 mb-5">
                <span
                  class="flex items-center justify-center w-7 h-7 rounded-full bg-blue-600 text-white text-xs font-bold shrink-0"
                  >2</span
                >
                <h2 class="text-base font-semibold text-slate-700">
                  Deal Details
                </h2>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="flex flex-col gap-1.5">
                  <label
                    class="text-xs font-semibold text-slate-600 uppercase tracking-wide"
                  >
                    Deal Name <span class="text-red-500">*</span>
                  </label>
                  <input
                    v-model="form.deal_name"
                    type="text"
                    placeholder="e.g. Annual Subscription"
                    :class="inputClass(errors.deal_name)"
                  />
                  <p
                    v-if="errors.deal_name"
                    class="text-xs text-red-600 flex items-center gap-1"
                  >
                    <svg
                      class="w-3 h-3 shrink-0"
                      fill="currentColor"
                      viewBox="0 0 20 20"
                    >
                      <path
                        fill-rule="evenodd"
                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                        clip-rule="evenodd"
                      />
                    </svg>
                    {{ errors.deal_name }}
                  </p>
                </div>

                <div class="flex flex-col gap-1.5">
                  <label
                    class="text-xs font-semibold text-slate-600 uppercase tracking-wide"
                  >
                    Deal Stage <span class="text-red-500">*</span>
                  </label>
                  <div class="relative">
                    <select
                      v-model="form.deal_stage"
                      :class="selectClass(errors.deal_stage)"
                    >
                      <option value="" disabled>Select stage...</option>
                      <option value="Qualification">Qualification</option>
                      <option value="Needs Analysis">Needs Analysis</option>
                      <option value="Value Proposition">
                        Value Proposition
                      </option>
                      <option value="Closed Won">Closed Won</option>
                      <option value="Closed Lost">Closed Lost</option>
                    </select>
                    <div
                      class="pointer-events-none absolute inset-y-0 right-3 flex items-center"
                    >
                      <svg
                        class="w-4 h-4 text-slate-400"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        viewBox="0 0 24 24"
                      >
                        <path
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          d="M19 9l-7 7-7-7"
                        />
                      </svg>
                    </div>
                  </div>
                  <p
                    v-if="errors.deal_stage"
                    class="text-xs text-red-600 flex items-center gap-1"
                  >
                    <svg
                      class="w-3 h-3 shrink-0"
                      fill="currentColor"
                      viewBox="0 0 20 20"
                    >
                      <path
                        fill-rule="evenodd"
                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                        clip-rule="evenodd"
                      />
                    </svg>
                    {{ errors.deal_stage }}
                  </p>
                </div>
              </div>
            </div>

            <div class="px-6 pb-2">
              <transition
                enter-active-class="transition-all duration-300 ease-out"
                enter-from-class="opacity-0 -translate-y-1"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition-all duration-200 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
              >
                <div
                  v-if="successMessage"
                  class="flex items-start gap-3 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm mb-4"
                >
                  <svg
                    class="w-5 h-5 shrink-0 text-emerald-500 mt-0.5"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                    />
                  </svg>
                  <span>{{ successMessage }}</span>
                </div>
              </transition>

              <transition
                enter-active-class="transition-all duration-300 ease-out"
                enter-from-class="opacity-0 -translate-y-1"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition-all duration-200 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
              >
                <div
                  v-if="errorMessage"
                  class="flex items-start gap-3 p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-sm mb-4"
                >
                  <svg
                    class="w-5 h-5 shrink-0 text-red-500 mt-0.5"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                    />
                  </svg>
                  <span>{{ errorMessage }}</span>
                </div>
              </transition>
            </div>

            <div class="px-6 pb-6">
              <button
                type="submit"
                :disabled="loading"
                class="w-full flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl bg-blue-600 hover:bg-blue-700 active:bg-blue-800 disabled:opacity-60 disabled:cursor-not-allowed text-white font-semibold text-sm transition-colors duration-200 shadow-sm"
              >
                <svg
                  v-if="loading"
                  class="animate-spin w-4 h-4"
                  fill="none"
                  viewBox="0 0 24 24"
                >
                  <circle
                    class="opacity-25"
                    cx="12"
                    cy="12"
                    r="10"
                    stroke="currentColor"
                    stroke-width="4"
                  />
                  <path
                    class="opacity-75"
                    fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"
                  />
                </svg>
                <svg
                  v-else
                  class="w-4 h-4"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="2"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M12 4v16m8-8H4"
                  />
                </svg>
                {{
                  loading ? "Creating record..." : "Create Record in Zoho CRM"
                }}
              </button>

              <p class="text-center text-xs text-slate-400 mt-3">
                Account and Deal will be linked automatically
              </p>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>
