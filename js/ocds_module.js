(function () {
    var Organisation = function() {
        this.identifier = {
            scheme: "CAC-RC",
            id: null,
            legalName: "",
            uri: "http://publicsearch.cac.gov.ng/comsearch/"
        };
        this.address = {
            streetAddress : "",
            locality: "",
            region: "",
            postalCode: "",
            countryName: "Nigeria"
        };
        this.contactPoint = {
            name : "",
            email: "",
            telephone: "",
            faxNumber: "",
            url: ""
        };
    }
   var Document = {
       id : "",
       documentType : "",
       title : "",
       description : "",
       url : "",
       datePublished : "",
       format : "",
       language : "en"
   }
    var Period = {
        startDate: "",
        endDate: "",
        maxExtentDate: null,
        durationInDays: null,
        calcDuration : function(){
            var start = new Date(this.startDate);
            var end = new Date(this.endDate);
            this.durationInDays = end - start;

        }
    }
    var Classification = {
        scheme: "",
        id: "",
        description: "",
        uri: ""
    }
    var Value = {
        amount: 0,
        currency: "NGN"
    }
    var Unit = {
        scheme: "",
        id : "",
        name: "",
        value: Value,
        uri: ""
    }
    var Milestone = {
        id : "",
        title: "",
        type : "",
        description: "",
        code: "",
        dueDate: "",
        dateMet: "",
        dateModified: "",
        status: "",
        documents: []
    }
    var Item = {
        id : "",
        description: "",
        classification: Classification,
        additionalClassification: [],
        quantity: 0,
        unit: Unit
    }
    var Budget = {
        id : "",
        description: "",
        amount: 0,
        project: "",
        projectId: 0,
        uri: ""
        
    }
    var Planning = {
        rationale: "",
        budget: Budget,
        milestone: Milestone
    }
    var Tender = {
        id : "",
        title:"",
        description: "",
        status: "",
        procuringEntity: Organisation,
        items : [],
        value : Value,
        minValue: Value,
        procurementMethod: "",
        procurementMethodDetails: "",
        procurementMethodRationale: "",
        mainProcurementCategory: "",
        additionalProcurementCategories: [],
        awardCriteria: [],
        awardCriteriaDetails: "",
        submissionMethod: [],
        submissionMethodDetails: "",
        tenderPeriod: Period,
        enquiryPeriod: Period,
        hasEnquiries: false,
        eligibilityCriteria: "",
        awardPeriod: Period,
        documents: [],
        milestones: [],
        amendments: []
        
    }
    var Amendment = {
        date:"",
        rationale: "",
        id: "",
        description: "",
        amendsReleaseId: "",
        releaseId: "",
        changes: ""
    }
    var Award = {
        id: "",
        title: "",
        description: "",
        status: "",
        date: "",
        value: Value,
        suppliers : [],
        items: [],
        contractPeriod: Period,
        documents: []
    }

})();